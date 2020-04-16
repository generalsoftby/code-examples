import moment from 'moment-timezone';

function convertTimeFormat(timeFormat, withSeconds = false)
{
    return ((timeFormat == 24) ? 'HH': 'hh') + ':mm' + (withSeconds ? ':ss' : '') + ((timeFormat == 24) ? '' : ' a');
}

class DateTimeFormatService
{
    constructor (dateFormat = 'YYYY-MM-DD', timeFormat = 24, timezone = moment.tz.guess())
    {
        this.dateFormat = dateFormat;
        this.timeFormat = timeFormat;
        this.timezone = timezone;
    }

    applyTimezone(value, options = {sourceTimezone: undefined})
    {
        return moment
            .tz(value, options.sourceTimezone || this.timezone)
            .tz(this.timezone)
            .format();
    }

    formatDate (value, options = {sourceTimezone: undefined})
    {
        return moment(this.applyTimezone(value, options))
            .tz(this.timezone)
            .format(this.dateFormat);
    }

    formatTime (value, options = {sourceTimezone: undefined, withSeconds: false})
    {
        return moment(this.applyTimezone(value, options))
            .tz(this.timezone)
            .format(convertTimeFormat(this.timeFormat, options.withSeconds));
    }

    formatDateTime (value, options = {sourceTimezone: undefined, withSeconds: false})
    {
        return this.formatDate(value, options) + ' ' + this.formatTime(value, options);
    }
}

export default DateTimeFormatService;
