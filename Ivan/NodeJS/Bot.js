import Crypto from 'crypto';
import Steam from 'steam';
import SteamCrypto from 'steam-crypto';
import SteamTradeOffers from 'steam-tradeoffers';
import SteamWeb from 'steam-web';
import SteamWebApi from 'steam-web-api';
import SteamCommunityMobileConfirmations from 'steamcommunity-mobile-confirmations';
import SteamTotp from 'steam-totp';

import Logging from './Logging';
import Db from './Db';
import Helper from './Helper';
import BotOffersManager from './BotOffersManager';
import BotCsgoManager from './BotCsgoManager';

let steamClient = undefined;
let steamUser = undefined;
let steamFriends = undefined;
let steamOffers = new SteamTradeOffers;
let steamCommunityMobileConfirmations = undefined;
let steamWeb = undefined;
const logging = Logging('Bot');
const db = new Db;
let botOffersManager = undefined;
let botCsgoManager = undefined;

class Bot
{
    constructor (botId)
    {
        logging && console.log('[Bot]');

        this.id = botId;

        this.steamOffersRequestsQueue = [];

        this.connected = false;
        this.connecting = false;
        setTimeout(() => this.connect(), parseInt(Math.random() * 15 * 1000));
        setInterval(() => this.connect(), parseInt(((Math.random() * 0.67) + 1.34) * 60 * 60 * 1000));

        this.confirming = false;
        this.lastConfirmingErrorAt = 0;
        setTimeout(() => this.confirm(), 1.67 * 60 * 1000);
        setInterval(() => this.confirm(), parseInt(((Math.random() * 15) + 30) * 60 * 1000));

        botOffersManager = new BotOffersManager(this);
    }

    connect ()
    {
        if (this.connecting)
        {
            return;
        }

        if (this.connected)
        {
            this.disconnect();
        }

        this.connecting = true;

        logging && console.log('[Bot][connect]');

        db.query('\
                SELECT \
                    `steamid`, \
                    `login`, \
                    `password`, \
                    `shared_secret`, \
                    `identity_secret`, \
                    `device_id`, \
                    `tradeurl`, \
                    `web_api_key`, \
                    "" \
                FROM `bots` \
                WHERE `id` = "' + this.id + '"; \
            ',
            (err, result) =>
            {
                if (err) throw err;

                let botAuthInfo = result[0];

                steamClient = new Steam.SteamClient();
                steamUser = new Steam.SteamUser(steamClient);
                steamFriends = new Steam.SteamFriends(steamClient);

                steamClient.on('debug', message => logging && console.log('[Bot][connect] steamClient.debug: ' + String(message)));
                steamClient.on('error', err =>
                {
                    console.warn('[Bot][connect] steamClient.error: ' + String(err));
                    this.disconnect();
                    setTimeout(() => this.connect(), 300000);
                    logging && console.log('[Bot][connect] setTimeout connect 300000');
                });
                steamClient.on('loggedOff', () => console.warn('[Bot][connect] steamClient.loggedOff'));

                steamClient.connect();

                steamClient.on('connected', () =>
                {
                    steamUser.logOn({
                        account_name: botAuthInfo.login,
                        password: botAuthInfo.password,
                        two_factor_code: SteamTotp.generateAuthCode(botAuthInfo.shared_secret),
                    });
                });

                steamClient.on('logOnResponse', response =>
                {
                    if (response.eresult != Steam.EResult.OK)
                    {
                        console.warn('[Bot][connect] steamClient.logOnResponse error: ' + JSON.stringify(response));
                        db.query('UPDATE `bots` SET `message` = "' + Helper.enumerationValueToString(Steam.EResult, response.eresult) + '" WHERE `id` = "' + this.id + '";');
                        this.connecting = false;
                        this.disconnect();
                        setTimeout(() => this.connect(), 300000);
                        logging && console.log('[Bot][connect] setTimeout connect 300000');
                        return;
                    }

                    logging && console.log('[Bot][connect] steamClient.logOnResponse OK: ' + JSON.stringify(response));

                    let sessionKey = SteamCrypto.generateSessionKey();
                    let loginKey = response.webapi_authenticate_user_nonce;

                    SteamWebApi('ISteamUserAuth')
                        .post('AuthenticateUser', 1,
                            {
                                steamid: steamClient.steamID,
                                sessionkey: sessionKey.encrypted,
                                encrypted_loginkey: SteamCrypto.symmetricEncrypt(new Buffer(loginKey), sessionKey.plain),
                            },
                            (statusCode, body) =>
                            {
                                if (statusCode !== 200)
                                {
                                    console.warn('[Bot][connect] SteamWebApi.ISteamUserAuth.AuthenticateUser error: ' + JSON.stringify(statusCode));
                                    db.query('UPDATE `bots` SET `message` = "SteamWebApi.ISteamUserAuth.AuthenticateUser error: ' + JSON.stringify(statusCode) + '" WHERE `id` = "' + this.id + '";');
                                    this.connecting = false;
                                    this.disconnect();
                                    setTimeout(() => this.connect(), 300000);
                                    logging && console.log('[Bot][connect] setTimeout connect 300000');
                                    return;
                                }

                                let sessionID = Crypto.randomBytes(12).toString('hex');
                                let webCookie = ['sessionid=' + sessionID, 'steamLogin=' + body.authenticateuser.token, 'steamLoginSecure=' + body.authenticateuser.tokensecure];

                                logging && console.log('[Bot][connect] SteamWebApi.ISteamUserAuth.AuthenticateUser: ' + JSON.stringify(sessionID) + ', ' + JSON.stringify(webCookie));

                                steamOffers.setup(
                                    {
                                        sessionID,
                                        webCookie: webCookie,
                                        APIKey:    botAuthInfo.web_api_key,
                                    });

                                steamCommunityMobileConfirmations = new SteamCommunityMobileConfirmations(
                                    {
                                        steamid:         steamClient.steamID,
                                        identity_secret: botAuthInfo.identity_secret,
                                        device_id:       botAuthInfo.device_id,
                                        webCookie:       webCookie
                                    });

                                steamWeb = new SteamWeb({
                                    apiKey: botAuthInfo.web_api_key,
                                    format: 'json',
                                });

                                steamFriends.setPersonaState(Steam.EPersonaState.Online);

                                db.query('UPDATE `bots` SET `message` = NULL WHERE `id` = "' + this.id + '";');

                                this.connecting = false;
                                this.connected = true;

                                handleCallsQueues();
                            });

                    botCsgoManager = new BotCsgoManager(steamClient);
                });
            });

        let handleCallsQueues = () =>
        {
            while (this.steamOffersRequestsQueue.length)
            {
                logging && console.log('[Bot][connect] Steam offers requests queue length is ' + this.steamOffersRequestsQueue.length + ', so need reduce');
                let steamOffersRequest = this.steamOffersRequestsQueue[0];
                this.steamOffersRequest(steamOffersRequest.method, steamOffersRequest.options, steamOffersRequest.callback);
                this.steamOffersRequestsQueue.splice(0, 1);
                logging && console.log('[Bot][connect] Steam offers requests queue length is reduced to ' + this.steamOffersRequestsQueue.length);
            }
        };
    }

    disconnect ()
    {
        if (this.connecting)
        {
            return;
        }

        logging && console.log('[Bot][disconnect]');
        steamClient.disconnect();
        this.connected = false;
    }

    confirm (callback)
    {
        if ( ! this.connected)
        {
            callback && callback(false);
            return;
        }

        if (this.confirming)
        {
            callback && callback(false);
            return;
        }

        if (Date.now() - this.lastConfirmingErrorAt < 1.67 * 60 * 1000)
        {
            callback && callback(false);
            return;
        }

        this.confirming = true;

        steamCommunityMobileConfirmations.FetchConfirmations((err, confirmations) =>
        {
            if (err)
            {
                console.warn('[Bot][confirm] FetchConfirmations error: ' + JSON.stringify(err));
                this.lastConfirmingErrorAt = Date.now();
                this.confirming = false;
                callback && callback(false);
                return;
            }

            logging && console.log('[Bot][confirm] FetchConfirmations received ' + confirmations.length + ' confirmations');

            if ( ! confirmations.length)
            {
                this.confirming = false;
                callback && callback(false);
                return;
            }

            steamCommunityMobileConfirmations.AcceptConfirmation(confirmations[0], (err, result) =>
            {
                if (err)
                {
                    console.warn('[Bot][confirm] AcceptConfirmation error: ' + JSON.stringify(err));
                    this.lastConfirmingErrorAt = Date.now();
                    this.confirming = false;
                    callback && callback(false);
                    return;
                }

                logging && console.log('[Bot][confirm] AcceptConfirmation result: ' + JSON.stringify(result));

                this.confirming = false;

                if (result && (confirmations.length > 1))
                {
                    setTimeout(() => this.confirm(callback), 0);
                }
                else
                {
                    callback && callback(true);
                }
            });
        });
    }

    steamOffersRequest (method, options, callback)
    {
        logging && console.log('[Bot][steamOffersRequest] ' + method);

        if ( ! this.connected)
        {
            logging && console.log('[Bot][steamOffersRequest] ' + method + ' request queueing');

            this.steamOffersRequestsQueue.push({method: method, options: options, callback: callback});
            return;
        }

        logging && console.log('[Bot][steamOffersRequest] ' + method + '(' + JSON.stringify(options) + ')');

        steamOffers[method](options, (err, result) =>
        {
            if (err)
            {
                logging && console.log('[Bot][steamOffersRequest] ' + method + ' error: ' + String(err));

                if ((String(err) == 'Error: 401') || (String(err) == 'Error: Can\'t get hold duration'))
                {
                    logging && console.log('[Bot][steamOffersRequest] ' + method + ' request queueing (after steamOffersRequest)');

                    this.steamOffersRequestsQueue.push({method: method, options: options, callback: callback});

                    this.connect();

                    return;
                }

                if (callback)
                {
                    callback(err);
                }

                return;
            }

            switch (method)
            {
                case 'makeOffer':
                    logging && console.log('[Bot][steamOffersRequest] ' + method + ' result: ' + JSON.stringify(result));
                    this.confirm();
                    break;
                case 'loadMyInventory':
                    logging && console.log('[Bot][steamOffersRequest] ' + method + ' result length: ' + JSON.stringify(result.length));
                    break;
                case 'getOffer':
                    logging && console.log('[Bot][steamOffersRequest] ' + method + ' result: ' + JSON.stringify((result && result.response && result.response.offer) ? Helper.enumerationValueToString(Helper.ETradeOfferState, result.response.offer.trade_offer_state) : result));
                    break;
                case 'getOffers':
                    logging && console.log('[Bot][steamOffersRequest] ' + method + ' result: ' + JSON.stringify((result && result.response) ? {sent: result.response.trade_offers_sent ? result.response.trade_offers_sent.length : result.response.trade_offers_sent, received: result.response.trade_offers_received ? result.response.trade_offers_received.length : result.response.trade_offers_received} : result));
                    break;
                case 'getItems':
                    logging && console.log('[Bot][steamOffersRequest] ' + method + ' result length: ' + JSON.stringify(result.length));
                    break;
                case 'cancelOffer':
                case 'acceptOffer':
                default:
                    logging && console.log('[Bot][steamOffersRequest] ' + method + ' result: ' + JSON.stringify(result));
            }

            if (callback)
            {
                callback(null, result);
            }
        });
    }
}

export default Bot;
