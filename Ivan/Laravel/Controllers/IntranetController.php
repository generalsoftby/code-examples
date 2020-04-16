<?php

namespace App\Http\Controllers;

use App\Repositories\IntranetMessagesRepository;
use App\Models\User;
use App\Models\Group;
use App\Models\IntranetMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IntranetController extends Controller
{
    /**
     * @param Request $request
     * @param IntranetMessagesRepository $intranetMessageRepository
     * @param string|null $requestReceiverType
     * @param int|null $requestReceiverId
     * @return Response
     */
    public function index(Request $request, IntranetMessagesRepository $intranetMessageRepository, $requestReceiverType = null, $requestReceiverId = null)
    {
        /** @var User $user */
        $user = $request->user();

        if ($request->ajax()) {
            $receiver = $intranetMessageRepository->findReceiverOrFail($user, $requestReceiverType, $requestReceiverId);
            $messages = $intranetMessageRepository->getMessages($user, $receiver, $request->input('lessThanId'));

            foreach ($messages as $message) {
                $intranetMessageRepository->deliveryMessage($user, $message);
            }

            return response()->json([
                'requestReceiverType' => $requestReceiverType,
                'requestReceiverId' => $requestReceiverId,
                'receiver' => $receiver,
                'user' => ($receiver instanceof User) ? $receiver : null,
                'group' => ($receiver instanceof Group) ? $receiver : null,
                'messages' => $messages,
            ]);
        }

        $groups = $user->groups;

        $latestDeliveries = $intranetMessageRepository->getLatestDeliveries($user);

        return view('intranet.index', compact(
            'latestDeliveries',
            'groups',
            'employees'
        ));
    }

    /**
     * @param Request $request
     * @param IntranetMessagesRepository $intranetMessageRepository
     * @return Response
     */
    public function message(Request $request, IntranetMessagesRepository $intranetMessageRepository)
    {
        $this->validate($request, [
            'message' => $request->hasFile('files') ? '' : 'required',
            'quoted_id' => 'exists:intranet_messages,id',
        ]);

        /** @var User $sender */
        $sender = $request->user();

        $receiver = $intranetMessageRepository->findReceiverOrFail($sender, $request->input('request_receiver_type'), $request->input('request_receiver_id'));

        $quoted = IntranetMessage::find($request->input('quoted_id'));

        $intranetMessage = $intranetMessageRepository->send($sender, $receiver, $request->input('message'), $quoted, null, $request->file('files'));

        return response()->json($intranetMessage);
    }

    /**
     * @param Request $request
     * @param IntranetMessagesRepository $intranetMessageRepository
     * @return Response
     */
    public function deliveryMessage(Request $request, IntranetMessagesRepository $intranetMessageRepository)
    {
        /** @var User $recipient */
        $recipient = $request->user();

        /** @var IntranetMessage $message */
        $message = IntranetMessage::findOrFail($request->input('message_id'));

        $intranetMessageRepository->deliveryMessage($recipient, $message);

        return response('');
    }
}
