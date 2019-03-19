<?php

require 'AllEvents.php';

class MyEvents extends AllEvents
{
    /**
      * This is a list of all current events. Uncomment the ones you wish to listen to.
      * Every event that is uncommented - should then have a function below.
      *
      * @var array
      */
     public $activeEvents = [
 //        'onAccountExpired',
 //        'onCallReceived',
 //        'onClose',
 //        'onCodeRegister',
 //        'onCodeRegisterFailed',
 //        'onCodeRequest',
 //        'onCodeRequestFailed',
 //        'onCodeRequestFailedTooManyGuesses',
 //        'onCodeRequestFailedTooRecent',
         'onConnect',
 //        'onConnectError',
 //        'onCredentialsBad',
 //        'onCredentialsGood',
         'onDisconnect',
 //        'onDissectPhone',
 //        'onDissectPhoneFailed',
 //        'onGetAudio',
 //        'onGetBroadcastLists',
 //        'onGetError',
 //        'onGetExtendAccount',
 //        'onGetFeature',
 //        'onGetGroupAudio',
 //        'onGetGroupImage',
 //        'onGetGroupLocation',
 //        'onGetGroupMessage',
 //        'onGetGroupV2Info',
 //        'onGetGroupVideo',
 //        'onGetGroups',
 //        'onGetGroupsSubject',
 //        'onGetGroupvCard',
 //        'onGetImage',
 //        'onGetLocation',
 //        'onGetMessage',
 //        'onGetNormalizedJid',
 //        'onGetPrivacyBlockedList',
 //        'onGetProfilePicture',
 //        'onGetReceipt',
 //        'onGetServerProperties',
 //        'onGetServicePricing',
 //        'onGetStatus',
 //        'onGetSyncResult',
 //        'onGetVideo',
 //        'onGetvCard',
 //        'onGroupCreate',
 //        'onGroupMessageComposing',
 //        'onGroupMessagePaused',
 //        'onGroupisCreated',
 //        'onGroupsChatCreate',
 //        'onGroupsChatEnd',
 //        'onGroupsParticipantChangedNumber',
 //        'onGroupsParticipantsAdd',
 //        'onGroupsParticipantsPromote',
 //        'onGroupsParticipantsRemove',
 //        'onLoginFailed',
 //        'onLoginSuccess',
 //        'onMediaMessageSent',
 //        'onMediaUploadFailed',
 //        'onMessageComposing',
 //        'onMessagePaused',
 //        'onMessageReceivedClient',
 //        'onMessageReceivedServer',
 //        'onNumberWasAdded',
 //        'onNumberWasRemoved',
 //        'onNumberWasUpdated',
 //        'onPaidAccount',
 //        'onPaymentRecieved',
 //        'onPing',
 //        'onPresenceAvailable',
 //        'onPresenceUnavailable',
 //        'onProfilePictureChanged',
 //        'onProfilePictureDeleted',
 //        'onSendMessage',
 //        'onSendMessageReceived',
 //        'onSendPong',
 //        'onSendPresence',
 //        'onSendStatusUpdate',
 //        'onStreamError',
 //        'onWebSync',
     ];

    public function onConnect($mynumber, $socket)
    {
        echo "<p>WooHoo!, Phone number $mynumber connected successfully!</p>";
    }

    public function onDisconnect($mynumber, $socket)
    {
        echo "<p>Booo!, Phone number $mynumber is disconnected!</p>";
    }
}
