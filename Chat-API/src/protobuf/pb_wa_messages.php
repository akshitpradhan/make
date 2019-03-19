<?php

require_once __DIR__.'/../func.php';
require_once __DIR__.'/../libaxolotl-php/protocol/SenderKeyDistributionMessage.php';
require_once __DIR__.'/../libaxolotl-php/ecc/Curve.php';

class WAMessage extends \ProtobufMessage
{
    const CONVERSATION = 1;
    const SKMSG = 2;
    const IMAGE = 3;
    const CONTACT = 4;
    const LOCATION = 5;
    const URLMSG = 6;
    const DOCUMENT = 7;
    const AUDIO = 8;
    const VIDEO = 9;
    const CALL = 10;
    const CHAT = 11;
    protected static $fields = [
    self::CONVERSATION => [
        'name'     => 'conversation',
        'required' => false,
        'type'     => 7,
    ],
    self::SKMSG => [
        'name'     => 'SKMSG',
        'required' => false,
        'type'     => 'SenderKeyGroupMessage',
    ],
    self::IMAGE => [
        'name'     => 'image',
        'required' => false,
        'type'     => 'ImageMessage',
    ],
    self::CONTACT => [
        'name'     => 'Contact',
        'required' => false,
        'type'     => 'ContactMessage',
    ],
    self::LOCATION => [
        'name'     => 'LOCATION',
        'required' => false,
        'type'     => 'Location',
    ],
    self::URLMSG => [
        'name'     => 'URLMSG',
        'required' => false,
        'type'     => 'MediaUrl',
    ],
    self::DOCUMENT => [
        'name'     => 'DOCUMENT',
        'required' => false,
        'type'     => 'DocumentMessage',
    ],
    self::AUDIO => [
        'name'     => 'AUDIO',
        'required' => false,
        'type'     => 'AudioMessage',
    ],
    self::VIDEO => [
        'name'     => 'VIDEO',
        'required' => false,
        'type'     => 'VideoMessage',
    ],
    self::CALL => [
        'name'     => 'CALL',
        'required' => false,
        'type'     => 'CallMessage',
    ],
    self::CHAT => [
        'name'     => 'CHAT',
        'required' => false,
        'type'     => 'ChatMessage',
    ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::CONVERSATION] = null;
      $this->values[self::SKMSG] = null;
      $this->values[self::IMAGE] = null;
      $this->values[self::CONTACT] = null;
      $this->values[self::LOCATION] = null;
      $this->values[self::URLMSG] = null;
      $this->values[self::DOCUMENT] = null;
      $this->values[self::AUDIO] = null;
      $this->values[self::VIDEO] = null;
      $this->values[self::CALL] = null;
      $this->values[self::CHAT] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    protected function WriteUInt64($val)
    {
        $num = ($val & 0x7f);
        $val = $val >> 7;
        $out = '';
        while ($val != 0) {
            $num = ($num | 0x80);
            $out .= chr($num);
            $num = ($val & 0x7f);
            $val = $val >> 7;
        }
        $out .= chr($num);

        return $out;
    }

    protected function WriteBytes($data)
    {
        $len = strlen($data);

        return $this->WriteUInt32($len).$data;
    }

    protected function ReadByte(&$input)
    {
        if (strlen($input) == 0) {
            return -1;
        }
        $ch = $input[0];
        $input = substr($input, 1);

        return $ch;
    }

    protected function ReadUInt32(&$input)
    {
        $num1 = 0;
        for ($x = 0; $x < 5; ++$x) {
            $num2 = ord($this->ReadByte($input));

            if ($num2 < 0) {
                throw new Exception('Stream ended too early');
            }
            if ($x == 4 && ($num2 & 240) != 0) {
                throw new Exception('Got larger VarInt than 32bit unsigned');
            }
            if (($num2 & 128) == 0) {
                return $num1 | ($num2 << 7 * $x);
            }
            $num1 |= (($num2 & 127) << 7 * $x);
        }
    }

    protected function ReadBytes(&$input)
    {
        $len = $this->ReadUInt32($input);
        $data = substr($input, 0, $len);
        $input = substr($input, $len);

        return $data;
    }

    public function parseFromString($input)
    {
        while (($key = $this->ReadByte($input)) != -1) {
            $key = ord($key);
            switch ($key) {
            case 10:
              $this->values[self::CONVERSATION] = $this->ReadBytes($input);
            break;
            case 18:
              $data = $this->ReadBytes($input);
              $skmsg = new SenderKeyGroupMessage();
              $skmsg->parseFromString($data);
              $this->values[self::SKMSG] = $skmsg;
            break;

            case 26:

              $data = $this->ReadBytes($input);
              $image = new ImageMessage();
              $image->parseFromString($data);
              $this->values[self::IMAGE] = $image;
            break;

            case 34:
              $data = $this->ReadBytes($input);
              $contact = new ContactMessage();
              $contact->parseFromString($data);
              $this->values[self::CONTACT] = $contact;
            break;

            case 42:
              $data = $this->ReadBytes($input);
              $loc = new Location();
              $loc->parseFromString($data);
              $this->values[self::LOCATION] = $loc;
            break;
            case 50:
              $data = $this->ReadBytes($input);
              $media = new MediaUrl();
              $media->parseFromString($data);
              $this->values[self::URLMSG] = $media;
            break;
            case 58:
              $data = $this->ReadBytes($input);
              $doc = new DocumentMessage();
              $doc->parseFromString($data);
              $this->values[self::DOCUMENT] = $doc;
            break;
            case 66:
              $data = $this->ReadBytes($input);
              $audio = new AudioMessage();
              $audio->parseFromString($data);
              $this->values[self::AUDIO] = $audio;
            break;
            case 74:
              $data = $this->ReadBytes($input);
              $video = new VideoMessage();
              $video->parseFromString($data);
              $this->values[self::VIDEO] = $video;
            break;
            case 82:
              $data = $this->ReadBytes($input);
              $call = new CallMessage();
              $call->parseFromString($data);
              $this->values[self::CALL] = $call;
            break;
            case 90:
              $data = $this->ReadBytes($input);
              $call = new ChatMessage();
              $call->parseFromString($data);
              $this->values[self::CHAT] = $call;
            break;
          }
        }
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::CONVERSATION] != null) {
            $out .= chr(10);
            $out .= $this->WriteBytes($this->values[self::CONVERSATION]);
        }
        if ($this->values[self::SKMSG] != null) {
            $out .= chr(18);
            $out .= $this->WriteBytes($this->values[self::SKMSG]->serializeToString());
        }
        if ($this->values[self::IMAGE] != null) {
            $out .= chr(26);
            $out .= $this->WriteBytes($this->values[self::IMAGE]->serializeToString());
        }
        if ($this->values[self::CONTACT] != null) {
            $out .= chr(34);
            $out .= $this->WriteBytes($this->values[self::CONTACT]->serializeToString());
        }
        if ($this->values[self::LOCATION] != null) {
            $out .= chr(42);
            $out .= $this->WriteBytes($this->values[self::LOCATION]->serializeToString());
        }
        if ($this->values[self::URLMSG] != null) {
            $out .= chr(50);
            $out .= $this->WriteBytes($this->values[self::URLMSG]->serializeToString());
        }
        if ($this->values[self::DOCUMENT] != null) {
            $out .= chr(58);
            $out .= $this->WriteBytes($this->values[self::DOCUMENT]->serializeToString());
        }
        if ($this->values[self::AUDIO] != null) {
            $out .= chr(66);
            $out .= $this->WriteBytes($this->values[self::AUDIO]->serializeToString());
        }
        if ($this->values[self::VIDEO] != null) {
            $out .= chr(74);
            $out .= $this->WriteBytes($this->values[self::VIDEO]->serializeToString());
        }
        if ($this->values[self::CALL] != null) {
            $out .= chr(82);
            $out .= $this->WriteBytes($this->values[self::CALL]->serializeToString());
        }
        if ($this->values[self::CHAT] != null) {
            $out .= chr(90);
            $out .= $this->WriteBytes($this->values[self::CHAT]->serializeToString());
        }

        return $out;
    }
}
class CallMessage extends \ProtobufMessage
{
    const CALLKEY = 1;
    protected static $fields = [
    self::CALLKEY => ['name' => 'callkey', 'required' => false, 'type' => 7],
  ];

    public function reset()
    {
        $this->values[self::CALLKEY] = null;
    }

    public function __construct()
    {
        $this->reset();
    }

    public function fields()
    {
        return self::$fields;
    }
}

class VideoMessage extends \ProtobufMessage
{
    /*public string Url { get; set; }
      public string Mimetype { get; set; }
      public byte[] FileSha256 { get; set; }
      public ulong FileLength { get; set; }
      public uint Seconds { get; set; }
      public byte[] MediaKey { get; set; }
      public byte[] JpegThumbnail { get; set; }*/
      const URL = 1;
    const MIMETYPE = 2;
    const SHA256 = 3;
    const LENGTH = 4;
    const SECONDS = 5;
    const REFKEY = 6;
    const CAPTION = 7;
    const THUMBNAIL = 8;

        /* @var array Field descriptors */
  protected static $fields = [
      self::URL => [
          'name'     => 'url',
          'required' => false,
          'type'     => 7,
      ],
      self::CAPTION => [
          'name'     => 'caption',
          'required' => false,
          'type'     => 7,
      ],
      self::MIMETYPE => [
          'name'     => 'mimetype',
          'required' => false,
          'type'     => 7,
      ],
      self::SHA256 => [
          'name'     => 'sha256',
          'required' => false,
          'type'     => 7,
      ],
      self::LENGTH => [
          'name'     => 'length',
          'required' => false,
          'type'     => 5,
      ],
      self::SECONDS => [
          'name'     => 'seconds',
          'required' => false,
          'type'     => 5,
      ],
      self::REFKEY => [
          'name'     => 'refkey',
          'required' => false,
          'type'     => 7,
      ],
      self::THUMBNAIL => [
          'name'     => 'thumbnail',
          'required' => false,
          'type'     => 5,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::URL] = null;
      $this->values[self::MIMETYPE] = null;
      $this->values[self::SHA256] = null;
      $this->values[self::LENGTH] = null;
      $this->values[self::SECONDS] = null;
      $this->values[self::REFKEY] = null;
      $this->values[self::THUMBNAIL] = null;
      $this->values[self::CAPTION] = null;
  }

    public function setUrl($newValue)
    {
        $this->values[self::URL] = $newValue;
    }

    public function setCaption($newValue)
    {
        $this->values[self::CAPTION] = $newValue;
    }

    public function setMimeType($newValue)
    {
        $this->values[self::MIMETYPE] = $newValue;
    }

    public function setSha256($newValue)
    {
        $this->values[self::SHA256] = $newValue;
    }

    public function setLength($newValue)
    {
        $this->values[self::LENGTH] = $newValue;
    }

    public function setSeconds($newValue)
    {
        $this->values[self::SECONDS] = $newValue;
    }

    public function setRefKey($newValue)
    {
        $this->values[self::REFKEY] = $newValue;
    }

    public function setThumbnail($newValue)
    {
        $this->values[self::THUMBNAIL] = $newValue;
    }

    public function getCaption()
    {
        return $this->values[self::CAPTION];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    protected function WriteUInt64($val)
    {
        $num = ($val & 0x7f);
        $val = $val >> 7;
        $out = '';
        while ($val != 0) {
            $num = ($num | 0x80);
            $out .= chr($num);
            $num = ($val & 0x7f);
            $val = $val >> 7;
        }
        $out .= chr($num);

        return $out;
    }

    protected function WriteBytes($data)
    {
        $len = strlen($data);

        return $this->WriteUInt32($len).$data;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::URL] != null) {
            $out .= chr(10);
            $out .= $this->WriteBytes($this->values[self::URL]);
        }
        if ($this->values[self::MIMETYPE] != null) {
            $out .= chr(18);
            $out .= $this->WriteBytes($this->values[self::MIMETYPE]);
        }
        if ($this->values[self::SHA256] != null) {
            $out .= chr(26);
            $out .= $this->WriteBytes($this->values[self::SHA256]);
        }
        $out .= chr(32);
        $out .= $this->WriteUInt64($this->values[self::LENGTH]);
        $out .= chr(40);
        $out .= $this->WriteUInt32($this->values[self::SECONDS]);
        if ($this->values[self::REFKEY] != null) {
            $out .= chr(50);
            $out .= $this->WriteBytes($this->values[self::REFKEY]);
        }
        if ($this->values[self::CAPTION] != null) {
            $out .= chr(58);
            $out .= $this->WriteBytes($this->values[self::CAPTION]);
        }
        if ($this->values[self::THUMBNAIL] != null) {
            $out .= chr(130);
            $out .= chr(1);
            $out .= $this->WriteBytes($this->values[self::THUMBNAIL]);
        }

        return $out;
    }
}
class SenderKeyGroupMessage extends \ProtobufMessage
{
    const GROUP_ID = 1;
    const SENDER_KEY = 2;
  /* @var array Field descriptors */
  protected static $fields = [
      self::GROUP_ID => [
          'name'     => 'group_id',
          'required' => false,
          'type'     => 7,
      ],
      self::SENDER_KEY => [
          'name'     => 'sender_key',
          'required' => false,
          'type'     => 7,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::GROUP_ID] = null;
      $this->values[self::SENDER_KEY] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getGroupId()
    {
        return $this->values[self::GROUP_ID];
    }

    public function getSenderKey()
    {
        return $this->values[self::SENDER_KEY];
    }

    public function setGroupId($id)
    {
        $this->values[self::GROUP_ID] = $id;
    }

    public function setSenderKey($sender_key)
    {
        $this->values[self::SENDER_KEY] = $sender_key;
    }
}
class SenderKeyGroupData extends \ProtobufMessage
{
    const MESSAGE = 1;
    const SENDER_KEY = 2;
  /* @var array Field descriptors */
  protected static $fields = [
      self::MESSAGE => [
        'name'     => 'message',
        'required' => false,
        'type'     => 7,
      ],
      self::SENDER_KEY => [
          'name'     => 'sender_key',
          'required' => false,
          'type'     => 'SenderKeyGroupMessage',
      ],

  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::MESSAGE] = null;
      $this->values[self::SENDER_KEY] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getMessage()
    {
        return $this->values[self::MESSAGE];
    }

    public function getSenderKey()
    {
        return $this->values[self::SENDER_KEY];
    }

    public function setMessage($data)
    {
        $this->values[self::MESSAGE] = $data;
    }

    public function setSenderKey($sender_key)
    {
        $this->values[self::SENDER_KEY] = $sender_key;
    }
}


class MediaUrl extends \ProtobufMessage
{
    /*
     Text
     MatchedText
     CanonicalUrl
     Description
     Title
     Thumbnail
  */
    const TEXT = 1; //full message with the url
    const MATCHEDTEXT = 2; // only the url
    const CANONICALURL = 3;
    const DESCRIPTION = 4; //Metadata description
    const TITLE = 5; //Page title
    const THUMBNAIL = 6;
    protected static $fields = [
        self::TEXT => [
            'name'     => 'message',
            'required' => false,
            'type'     => 7,
        ],
        self::MATCHEDTEXT => [
            'name'     => 'url',
            'required' => false,
            'type'     => 7,
        ],
        self::CANONICALURL => [
            'name'     => 'CANONICALURL',
            'required' => false,
            'type'     => 7,
        ],
        self::DESCRIPTION => [
            'name'     => 'description',
            'required' => false,
            'type'     => 7,
        ],
        self::TITLE => [
            'name'     => 'title',
            'required' => false,
            'type'     => 7,
        ],
        self::THUMBNAIL => [
            'name'     => 'thumbnail',
            'required' => false,
            'type'     => 7,
        ],
    ];

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Clears message values and sets default ones.
     *
     * @return null
     */
    public function reset()
    {
        $this->values[self::TEXT] = null;
        $this->values[self::MATCHEDTEXT] = null;
        $this->values[self::CANONICALURL] = null;
        $this->values[self::DESCRIPTION] = null;
        $this->values[self::TITLE] = null;
        $this->values[self::THUMBNAIL] = null;
    }

    /**
     * Returns field descriptors.
     *
     * @return array
     */
    public function fields()
    {
        return self::$fields;
    }

    public function getMessage()
    {
        return $this->values[self::TEXT];
    }

    public function getUrl()
    {
        return $this->values[self::MATCHEDTEXT];
    }

    public function getCanonical()
    {
        return $this->values[self::CANONICALURL];
    }

    public function getDescription()
    {
        return $this->values[self::DESCRIPTION];
    }

    public function getTitle()
    {
        return $this->values[self::TITLE];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

    public function setMessage($value)
    {
        $this->values[self::TEXT] = $value;
    }

    public function setUrl($value)
    {
        $this->values[self::MATCHEDTEXT] = $value;
    }

    public function setCanonical($value)
    {
        $this->values[self::CANONICALURL] = $value;
    }

    public function setDescription($value)
    {
        $this->values[self::DESCRIPTION] = $value;
    }

    public function setTitle($value)
    {
        $this->values[self::TITLE] = $value;
    }

    public function setThumbnail($value)
    {
        $this->values[self::THUMBNAIL] = $value;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    protected function WriteBytes($data)
    {
        return $this->WriteUInt32(strlen($data)).$data;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::TEXT]) {
            $out .= chr(10).$this->WriteBytes($this->values[self::TEXT]);
        }
        if ($this->values[self::MATCHEDTEXT]) {
            $out .= chr(18).$this->WriteBytes($this->values[self::MATCHEDTEXT]);
        }
        if ($this->values[self::CANONICALURL]) {
            $out .= chr(34).$this->WriteBytes($this->values[self::CANONICALURL]);
        }
        if ($this->values[self::DESCRIPTION]) {
            $out .= chr(42).$this->WriteBytes($this->values[self::DESCRIPTION]);
        }
        if ($this->values[self::TITLE]) {
            $out .= chr(50).$this->WriteBytes($this->values[self::TITLE]);
        }
        if ($this->values[self::THUMBNAIL]) {
            $out .= chr(130).chr(1).$this->WriteBytes($this->values[self::THUMBNAIL]);
        }

        return $out;
    }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }
}
class ImageMessage extends \ProtobufMessage
{
    const URL = 1;
    const MIMETYPE = 2;
    const CAPTION = 3;
    const SHA256 = 4;
    const LENGTH = 5;
    const HEIGHT = 6;
    const WIDTH = 7;
    const REFKEY = 8;
    const THUMBNAIL = 9;
  /* @var array Field descriptors */
  protected static $fields = [
      self::URL => [
          'name'     => 'url',
          'required' => false,
          'type'     => 7,
      ],
      self::MIMETYPE => [
          'name'     => 'mimetype',
          'required' => false,
          'type'     => 7,
      ],
      self::CAPTION => [
          'name'     => 'caption',
          'required' => false,
          'type'     => 7,
      ],
      self::SHA256 => [
          'name'     => 'sha256',
          'required' => false,
          'type'     => 7,
      ],
      self::LENGTH => [
          'name'     => 'length',
          'required' => false,
          'type'     => 5,
      ],
      self::HEIGHT => [
          'name'     => 'height',
          'required' => false,
          'type'     => 5,
      ],
      self::WIDTH => [
          'name'     => 'width',
          'required' => false,
          'type'     => 5,
      ],
      self::REFKEY => [
          'name'     => 'refkey',
          'required' => false,
          'type'     => 7,
      ],
      self::THUMBNAIL => [
          'name'     => 'thumbnail',
          'required' => false,
          'type'     => 7,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::URL] = null;
      $this->values[self::MIMETYPE] = null;
      $this->values[self::CAPTION] = null;
      $this->values[self::SHA256] = null;
      $this->values[self::LENGTH] = null;
      $this->values[self::HEIGHT] = null;
      $this->values[self::WIDTH] = null;
      $this->values[self::REFKEY] = null;
      $this->values[self::THUMBNAIL] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getUrl()
    {
        return $this->values[self::URL];
    }

    public function getMimeType()
    {
        return $this->values[self::MIMETYPE];
    }

    public function getCaption()
    {
        return $this->values[self::CAPTION];
    }

    public function getSha256()
    {
        return $this->values[self::SHA256];
    }

    public function getLength()
    {
        return $this->values[self::LENGTH];
    }

    public function getHeight()
    {
        return $this->values[self::HEIGHT];
    }

    public function getWidth()
    {
        return $this->values[self::WIDTH];
    }

    public function getRefKey()
    {
        return $this->values[self::REFKEY];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

    public function setUrl($newValue)
    {
        $this->values[self::URL] = $newValue;
    }

    public function setMimeType($newValue)
    {
        $this->values[self::MIMETYPE] = $newValue;
    }

    public function setCaption($newValue)
    {
        $this->values[self::CAPTION] = $newValue;
    }

    public function setSha256($newValue)
    {
        $this->values[self::SHA256] = $newValue;
    }

    public function setLength($newValue)
    {
        $this->values[self::LENGTH] = $newValue;
    }

    public function setHeight($newValue)
    {
        $this->values[self::HEIGHT] = $newValue;
    }

    public function setWidth($newValue)
    {
        $this->values[self::WIDTH] = $newValue;
    }

    public function setRefKey($newValue)
    {
        $this->values[self::REFKEY] = $newValue;
    }

    public function setThumbnail($newValue)
    {
        $this->values[self::THUMBNAIL] = $newValue;
    }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    protected function WriteUInt64($val)
    {
        $num = ($val & 0x7f);
        $val = $val >> 7;
        $out = '';
        while ($val != 0) {
            $num = ($num | 0x80);
            $out .= chr($num);
            $num = ($val & 0x7f);
            $val = $val >> 7;
        }
        $out .= chr($num);

        return $out;
    }

    protected function WriteBytes($data)
    {
        $len = strlen($data);

        return $this->WriteUInt32($len).$data;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::URL] != null) {
            $out .= chr(10);
            $out .= $this->WriteBytes($this->values[self::URL]);
        }
        if ($this->values[self::MIMETYPE] != null) {
            $out .= chr(18);
            $out .= $this->WriteBytes($this->values[self::MIMETYPE]);
        }
        if ($this->values[self::CAPTION] != null) {
            $out .= chr(26);
            $out .= $this->WriteBytes($this->values[self::CAPTION]);
        }
        if ($this->values[self::SHA256] != null) {
            $out .= chr(34);
            $out .= $this->WriteBytes($this->values[self::SHA256]);
        }
        $out .= chr(40);
        $out .= $this->WriteUInt64($this->values[self::LENGTH]);
        $out .= chr(48);
        $out .= $this->WriteUInt64($this->values[self::HEIGHT]);
        $out .= chr(56);
        $out .= $this->WriteUInt64($this->values[self::WIDTH]);

        if ($this->values[self::REFKEY] != null) {
            $out .= chr(66);
            $out .= $this->WriteBytes($this->values[self::REFKEY]);
        }
        if ($this->values[self::THUMBNAIL] != null) {
            $out .= chr(130);
            $out .= chr(1);
            $out .= $this->WriteBytes($this->values[self::THUMBNAIL]);
        }

        return $out;
    }
}


class Location extends \ProtobufMessage
{
    const LATITUDE = 1;
    const LONGITUDE = 2;
    const NAME = 3;
    const DESCRIPTION = 4;
    const URL = 5;
    const THUMBNAIL = 6;
    /* @var array Field descriptors */
    protected static $fields = [
      self::LATITUDE => [
          'name'     => 'Latitude',
          'required' => false,
          'type'     => 1,
      ],
      self::LONGITUDE => [
          'name'     => 'Longitude',
          'required' => false,
          'type'     => 1,
      ],
      self::NAME => [
          'name'     => 'Name',
          'required' => false,
          'type'     => 7,
      ],
      self::DESCRIPTION => [
          'name'     => 'Description',
          'required' => false,
          'type'     => 7,
      ],
      self::URL => [
          'name'     => 'Url',
          'required' => false,
          'type'     => 7,
      ],
      self::THUMBNAIL => [
          'name'     => 'Thumbnail',
          'required' => false,
          'type'     => 7,
      ],

    ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::LATITUDE] = null;
      $this->values[self::LONGITUDE] = null;
      $this->values[self::NAME] = null;
      $this->values[self::DESCRIPTION] = null;
      $this->values[self::URL] = null;
      $this->values[self::THUMBNAIL] = null;
  }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getLatitude()
    {
        return $this->values[self::LATITUDE];
    }

    public function getLongitude()
    {
        return $this->values[self::LONGITUDE];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

    public function getName()
    {
        return $this->values[self::NAME];
    }

    public function getDescription()
    {
        return $this->values[self::DESCRIPTION];
    }

    public function getUrl()
    {
        return $this->values[self::URL];
    }

    public function setName($value)
    {
        $this->values[self::NAME] = $value;
    }

    public function setDescription($value)
    {
        $this->values[self::DESCRIPTION] = $value;
    }

    public function setUrl($value)
    {
        $this->values[self::URL] = $value;
    }

    public function setLatitude($value)
    {
        $this->values[self::LATITUDE] = $value;
    }

    public function setLongitude($value)
    {
        $this->values[self::LONGITUDE] = $value;
    }

    public function setThumbnail($value)
    {
        $this->values[self::THUMBNAIL] = $value;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $thumb = $this->getThumbnail();
        $this->setThumbnail(null);
        $data = parent::serializeToString();
        $data .= hex2bin('8201');
        $data .= $this->WriteUInt32(strlen($thumb));
        $data .= $thumb;
        $this->setThumbnail($thumb);

        return $data;
    }
}


/* May start with 01 thats bad */
class DocumentMessage extends \ProtobufMessage
{
    const URL = 1;
    const MIMETYPE = 2;
    const NAME = 3;
    const SHA256 = 4;
    const LENGTH = 5;
    const PAGES = 6;
    const REFKEY = 7;
    const FILENAME = 8;
    const THUMBNAIL = 9;
    /* @var array Field descriptors */
  protected static $fields = [
      self::URL => [
          'name'     => 'url',
          'required' => false,
          'type'     => 7,
      ],
      self::MIMETYPE => [
          'name'     => 'mimetype',
          'required' => false,
          'type'     => 7,
      ],
      self::NAME => [
          'name'     => 'name',
          'required' => false,
          'type'     => 7,
      ],
      self::LENGTH => [
          'name'     => 'length',
          'required' => false,
          'type'     => 5,
      ],
      self::SHA256 => [
          'name'     => 'sha256',
          'required' => false,
          'type'     => 7,
      ],
      self::PAGES => [
          'name'     => 'PAGES',
          'required' => false,
          'type'     => 5,
      ],
      self::REFKEY => [
          'name'     => 'refkey',
          'required' => false,
          'type'     => 7,
      ],
      self::FILENAME => [
          'name'     => 'filename',
          'required' => false,
          'type'     => 7,
      ],
      self::THUMBNAIL => [
          'name'     => 'thumbnail',
          'required' => false,
          'type'     => 7,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::URL] = null;
      $this->values[self::MIMETYPE] = null;
      $this->values[self::NAME] = null;
      $this->values[self::LENGTH] = null;
      $this->values[self::SHA256] = null;
      $this->values[self::PAGES] = null;
      $this->values[self::REFKEY] = null;
      $this->values[self::FILENAME] = null;
      $this->values[self::THUMBNAIL] = null;
  }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    public function getUrl()
    {
        return $this->values[self::URL];
    }

    public function getMimeType()
    {
        return $this->values[self::MIMETYPE];
    }

    public function getLength()
    {
        return $this->values[self::LENGTH];
    }

    public function getName()
    {
        return $this->values[self::NAME];
    }

    public function getPages()
    {
        return $this->values[self::PAGES];
    }

    public function getRefKey()
    {
        return $this->values[self::REFKEY];
    }

    public function getFilename()
    {
        return $this->values[self::FILENAME];
    }

    public function getThumbnail()
    {
        return $this->values[self::THUMBNAIL];
    }

    public function getCaption()
    {
    }

    public function setUrl($newValue)
    {
        $this->values[self::URL] = $newValue;
    }

    public function setMimeType($newValue)
    {
        $this->values[self::MIMETYPE] = $newValue;
    }

    public function setName($newValue)
    {
        $this->values[self::NAME] = $newValue;
    }

    public function setSha256($newValue)
    {
        $this->values[self::SHA256] = $newValue;
    }

    public function setLength($newValue)
    {
        $this->values[self::LENGTH] = $newValue;
    }

    public function setRefKey($newValue)
    {
        $this->values[self::REFKEY] = $newValue;
    }

    public function setThumbnail($newValue)
    {
        $this->values[self::THUMBNAIL] = $newValue;
    }

    public function setPages($newValue)
    {
        return $this->values[self::PAGES] = $newValue;
    }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $this->setThumbnail(stristr($data, hex2bin('ffd8ffe0')));
    }
}
/* May start with 01 thats bad */
class ContactMessage extends \ProtobufMessage
{
    const NAME = 1;
    const VCARD = 2;

    /* @var array Field descriptors */
  protected static $fields = [
      self::NAME => [
          'name'     => 'name',
          'required' => false,
          'type'     => 7,
      ],
      self::VCARD => [
          'name'     => 'vcard',
          'required' => false,
          'type'     => 7,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

  /**
   * Clears message values and sets default ones.
   *
   * @return null
   */
  public function reset()
  {
      $this->values[self::NAME] = null;
      $this->values[self::VCARD] = null;
  }

    public function setName($newValue)
    {
        $this->values[self::NAME] = $newValue;
    }

    public function setVcard($newValue)
    {
        $this->values[self::VCARD] = $newValue;
    }

    public function getVcard()
    {
        return $this->values[self::VCARD];
    }

    public function getName()
    {
        return $this->values[self::NAME];
    }

  /**
   * Returns field descriptors.
   *
   * @return array
   */
  public function fields()
  {
      return self::$fields;
  }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function parseFromString($data)
    {
        parent::parseFromString($data);
        $pos = strpos($data, hex2bin('8201'));
        $vcard = substr($data, $pos + 2);
        $vcard = substr($vcard, strpos($vcard, 'BE'));
        $this->values[self::VCARD] = $vcard;
    }

    public function serializeToString()
    {
        $vcard = $this->values[self::VCARD];
        $this->values[self::VCARD] = null;
        $data = parent::serializeToString();
        $data .= hex2bin('8201');
        $data .= $this->WriteUInt32(strlen($vcard));
        $data .= $vcard;
        $this->values[self::VCARD] = $vcard;

        return $data;
    }
}

class ClientPayload extends ProtobufMessage
{
    const CLIENT_FEATURES = 1;
    const LEGACY_PASSWORD = 2;
    const PASSIVE = 3;
    const PUSHNAME = 4;
    const USERAGENT = 5;
    const USERNAME = 6;
    const WEBINFO = 7;

    protected static $fields = [
      self::CLIENT_FEATURES => [
          'name'     => 'features',
          'repeated' => true,
          'type'     => 5,
      ],
      self::LEGACY_PASSWORD => [
          'name'     => 'legacy_password',
          'required' => false,
          'type'     => 7,
      ],
      self::PASSIVE => [
        'name'     => 'passive',
        'type'     => 8,
        'required' => false,
      ],
      self::PUSHNAME => [
        'name'     => 'pushname',
        'type'     => 7,
        'required' => false,
      ],
      self::USERAGENT => [
        'name'     => 'useragent',
        'type'     => 'UserAgent',
        'required' => false,
      ],
      self::USERNAME => [
        'name'     => 'username',
        'type'     => 5,
        'required' => false,
      ],
      self::WEBINFO => [
          'name'     => 'WEBINFO',
          'type'     => 'WebInfo',
          'required' => false,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::CLIENT_FEATURES] = [];
        $this->values[self::LEGACY_PASSWORD] = null;
        $this->values[self::PASSIVE] = false;
        $this->values[self::PUSHNAME] = null;
        $this->values[self::USERAGENT] = null;
        $this->values[self::USERNAME] = null;
        $this->values[self::WEBINFO] = null;
    }

    public function fields()
    {
        return self::$fields;
    }

    protected function WriteUInt64($val)
    {
        $num = ($val & 0x7f);
        $val = $val >> 7;
        $out = '';
        while ($val != 0) {
            $num = ($num | 0x80);
            $out .= chr($num);
            $num = ($val & 0x7f);
            $val = $val >> 7;
        }
        $out .= chr($num);

        return $out;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::USERNAME] != null) {
            $out .= chr(8);
            $out .= $this->WriteUInt64($this->values[self::USERNAME]);
        }
        if ($this->values[self::LEGACY_PASSWORD] != null) {
            $out .= chr(0x12);
            $out .= $this->WriteUInt32(strlen($this->values[self::LEGACY_PASSWORD]));
            $out .= $this->values[self::LEGACY_PASSWORD];
        }


        $out .= chr(0x18);
        $out .= ($this->values[self::PASSIVE] ? chr(1) : chr(0));

        if ($this->values[self::CLIENT_FEATURES] != null && is_array($this->values[self::CLIENT_FEATURES]) && count($this->values[self::CLIENT_FEATURES]) > 0) {
            foreach ($this->values[self::CLIENT_FEATURES] as $k => $v) {
                $out .= chr(0x20);
                $out .= $this->WriteUInt64($v);
            }
        }
        if ($this->values[self::USERAGENT] != null) {
            $out .= chr(0x2a);
            $ua = $this->values[self::USERAGENT]->serializeToString();
            $out .= $this->WriteUInt32(strlen($ua));
            $out .= $ua;
        }
        if ($this->values[self::WEBINFO] != null) {
            $out .= chr(50);
            $wi = $this->values[self::WEBINFO]->serializeToString();
            $out .= $this->WriteUInt32(strlen($wi));
            $out .= $wi;
        }
        if ($this->values[self::PUSHNAME] != null) {
            $out .= chr(0x3a);
            $out .= $this->WriteUInt32(strlen($this->values[self::PUSHNAME]));
            $out .= $this->values[self::PUSHNAME];
        }

        return $out;
    }
}

class UserAgent extends ProtobufMessage
{
    const APPVERSION = 1;
    const DEVICE = 2;
    const COUNTRYCODE = 3;
    const LANGUAGECODE = 4;
    const MANUFACTURER = 5;
    const MCC = 6;
    const MNC = 7;
    const OSBUILDNUMBER = 8;
    const OSVERSION = 9;
    const PHONEID = 10;
    const PLATFORM = 11;
    const RELEASECHANNEL = 12;
    protected static $fields = [
      self::APPVERSION => [
          'name'     => 'App_version',
          'required' => false,
          'type'     => 'AppVersion',
      ],
      self::DEVICE => [
          'name'     => 'Device',
          'required' => false,
          'type'     => 7,
      ],
      self::COUNTRYCODE => [
        'name'     => 'CountryCode',
        'type'     => 7,
        'required' => false,
      ],
      self::LANGUAGECODE => [
        'name'     => 'LanguageCode',
        'type'     => 7,
        'required' => false,
      ],
      self::MANUFACTURER => [
        'name'     => 'Manufacturer',
        'type'     => 7,
        'required' => false,
      ],
      self::MCC => [
        'name'     => 'Mcc',
        'type'     => 7,
        'required' => false,
      ],
      self::MNC => [
          'name'     => 'Mnc',
          'type'     => 7,
          'required' => false,
      ],
      self::OSBUILDNUMBER => [
          'name'     => 'Osbuild',
          'type'     => 7,
          'required' => false,
      ],
      self::OSVERSION => [
          'name'     => 'OsVersion',
          'type'     => 7,
          'required' => false,
      ],
      self::PHONEID => [
          'name'     => 'PhoneId',
          'type'     => 7,
          'required' => false,
      ],
      self::PLATFORM => [
          'name'     => 'platform',
          'type'     => 5,
          'required' => false,
      ],
      self::RELEASECHANNEL => [
          'name'     => 'ReleaseChannel',
          'type'     => 5,
          'required' => false,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::APPVERSION] = null;
        $this->values[self::DEVICE] = null;
        $this->values[self::COUNTRYCODE] = false;
        $this->values[self::LANGUAGECODE] = null;
        $this->values[self::MANUFACTURER] = null;
        $this->values[self::MCC] = null;
        $this->values[self::MNC] = null;
        $this->values[self::OSBUILDNUMBER] = null;
        $this->values[self::OSVERSION] = null;
        $this->values[self::PHONEID] = null;
        $this->values[self::PLATFORM] = null;
        $this->values[self::RELEASECHANNEL] = null;
    }

    public function fields()
    {
        return self::$fields;
    }

    protected function WriteUInt64($val)
    {
        $num = ($val & 0x7f);
        $val = $val >> 7;
        $out = '';
        while ($val != 0) {
            $num = ($num | 0x80);
            $out .= chr($num);
            $num = ($val & 0x7f);
            $val = $val >> 7;
        }
        $out .= chr($num);

        return $out;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::PLATFORM] != null) {
            $out .= chr(8);
            $out .= $this->WriteUInt64($this->values[self::PLATFORM]);
        }
        if ($this->values[self::APPVERSION] != null) {
            $out .= chr(0x12);
            $appver = $this->values[self::APPVERSION]->serializeToString();
            $out .= $this->WriteUInt32(strlen($appver));
            $out .= $appver;
        }
        if ($this->values[self::MCC] != null) {
            $out .= chr(0x1a);
            $out .= $this->WriteUInt32(strlen($this->values[self::MCC]));
            $out .= $this->values[self::MCC];
        }
        if ($this->values[self::MNC] != null) {
            $out .= chr(0x22);
            $out .= $this->WriteUInt32(strlen($this->values[self::MNC]));
            $out .= $this->values[self::MNC];
        }
        if ($this->values[self::OSVERSION] != null) {
            $out .= chr(0x2a);
            $out .= $this->WriteUInt32(strlen($this->values[self::OSVERSION]));
            $out .= $this->values[self::OSVERSION];
        }
        if ($this->values[self::MANUFACTURER] != null) {
            $out .= chr(50);
            $out .= $this->WriteUInt32(strlen($this->values[self::MANUFACTURER]));
            $out .= $this->values[self::MANUFACTURER];
        }
        if ($this->values[self::DEVICE] != null) {
            $out .= chr(0x3a);
            $out .= $this->WriteUInt32(strlen($this->values[self::DEVICE]));
            $out .= $this->values[self::DEVICE];
        }
        if ($this->values[self::OSBUILDNUMBER] != null) {
            $out .= chr(0x42);
            $out .= $this->WriteUInt32(strlen($this->values[self::OSBUILDNUMBER]));
            $out .= $this->values[self::OSBUILDNUMBER];
        }
        if ($this->values[self::PHONEID] != null) {
            $out .= chr(0x4a);
            $out .= $this->WriteUInt32(strlen($this->values[self::PHONEID]));
            $out .= $this->values[self::PHONEID];
        }
        if ($this->values[self::RELEASECHANNEL]) {
            $out .= chr(80);
            $out .= $this->WriteUInt64($this->values[self::RELEASECHANNEL]);
        }
        if ($this->values[self::LANGUAGECODE] != null) {
            $out .= chr(90);
            $out .= $this->WriteUInt32(strlen($this->values[self::LANGUAGECODE]));
            $out .= $this->values[self::LANGUAGECODE];
        }
        if ($this->values[self::COUNTRYCODE] != null) {
            $out .= chr(0x62);
            $out .= $this->WriteUInt32(strlen($this->values[self::COUNTRYCODE]));
            $out .= $this->values[self::COUNTRYCODE];
        }

        return $out;
    }
}
class AppVersion extends ProtobufMessage
{
    const PRIMARY = 1;
    const SECONDARY = 2;
    const TERTIARY = 3;
    const QUATERNARY = 4;
    protected static $fields = [
      self::PRIMARY => [
          'name'     => 'Primary',
          'required' => false,
          'type'     => 5,
      ],
      self::SECONDARY => [
          'name'     => 'Secondary',
          'required' => false,
          'type'     => 5,
      ],
      self::TERTIARY => [
        'name'     => 'Tertiary',
        'type'     => 5,
        'required' => false,
      ],
      self::QUATERNARY => [
        'name'     => 'Quaternary',
        'type'     => 5,
        'required' => false,
      ],
  ];

    public function __construct()
    {
        $this->reset();
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function reset()
    {
        $this->values[self::PRIMARY] = null;
        $this->values[self::SECONDARY] = null;
        $this->values[self::TERTIARY] = null;
        $this->values[self::QUATERNARY] = null;
    }

    public function fields()
    {
        return self::$fields;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::PRIMARY] != null) {
            $out .= chr(8);
            $out .= $this->WriteUInt32($this->values[self::PRIMARY]);
        }
        if ($this->values[self::SECONDARY] != null) {
            $out .= chr(16);
            $out .= $this->WriteUInt32($this->values[self::SECONDARY]);
        }
        if ($this->values[self::TERTIARY] != null) {
            $out .= chr(24);
            $out .= $this->WriteUInt32($this->values[self::TERTIARY]);
        }
        if ($this->values[self::QUATERNARY] != null) {
            $out .= chr(32);
            $out .= $this->WriteUInt32($this->values[self::QUATERNARY]);
        }

        return $out;
    }
}

class Platform
{
    const ANDROID = 1;
    const IOS = 2;
    const WINDOWS_PHONE = 3;
    const BLACKBERRY = 3;
    const BLACKBERRYX = 4;
    const S40 = 5;
    const S60 = 6;
    const PYTHON_CLIENT = 7;
    const TIZEN = 8;
}

class ReleaseChannel
{
    const RELEASE = 1;
    const BETA = 2;
    const ALPHA = 3;
    const DEBUG = 3;
}

class NoiseCertificate extends ProtobufMessage
{
    const DETAILS = 1;
    const SIGNATURE = 2;
    protected static $fields = [
    self::DETAILS => [
      'type'     => 7,
      'name'     => 'Details',
      'required' => false,
    ],
    self::SIGNATURE => [
      'type'     => 7,
      'name'     => 'signature',
      'required' => false,
    ],
  ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::DETAILS] = null;
        $this->values[self::SIGNATURE] = null;
    }

    public function fields()
    {
        return self::$fields;
    }
}
class NoiseCertificate_Details extends ProtobufMessage
{
    const EXPIRE = 1;
    const ISSUER = 2;
    const SUBJECT = 3;
    const SERIAL = 4;
    const KEY = 5;

    protected static $fields = [
    self::EXPIRE => [
      'type'     => 5,
      'name'     => 'Expire',
      'required' => false,
    ],
    self::ISSUER => [
      'type'     => 7,
      'name'     => 'Issuer',
      'required' => false,
    ],
    self::KEY => [
      'type'     => 7,
      'name'     => 'Key',
      'required' => false,
    ],
    self::SERIAL => [
      'type'     => 7,
      'name'     => 'Serial',
      'required' => false,
    ],
    self::SUBJECT => [
      'type'     => 7,
      'name'     => 'Subject',
      'required' => false,
    ],
  ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::EXPIRE] = null;
        $this->values[self::ISSUER] = null;
        $this->values[self::KEY] = null;
        $this->values[self::SERIAL] = null;
        $this->values[self::SUBJECT] = null;
    }

    public function fields()
    {
        return self::$fields;
    }
}

class HandShake extends ProtobufMessage
{
    const CLIENT_FINISH = 1;
    const CLIENT_HELLO = 2;
    const SERVER_HELLO = 3;
    protected static $fields = [
      self::CLIENT_FINISH => [
          'name'     => 'ClientFinish',
          'type'     => 'ClientFinish',
          'required' => false,
      ],
      self::CLIENT_HELLO => [
          'name'     => 'ClientHello',
          'type'     => 'ClientHello',
          'required' => false,
      ],
      self::SERVER_HELLO => [
          'name'     => 'ServerHello',
          'type'     => 'ServerHello',
          'required' => false,
      ],
    ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::CLIENT_HELLO] = null;
        $this->values[self::CLIENT_FINISH] = null;
        $this->values[self::SERVER_HELLO] = null;
    }

    public function fields()
    {
        return self::$fields;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::CLIENT_HELLO] != null) {
            $out .= chr(0x12);
            $hello = $this->values[self::CLIENT_HELLO]->serializeToString();
            $out .= $this->WriteUInt32(strlen($hello));
            $out .= $hello;
        }
        if ($this->values[self::SERVER_HELLO] != null) {
            $out .= chr(0x1a);
            $shello = $this->values[self::SERVER_HELLO]->serializeToString();
            $out .= $this->WriteUInt32(strlen($shello));
            $out .= $shello;
        }
        if ($this->values[self::CLIENT_FINISH] != null) {
            $out .= chr(0x22);
            $finish = $this->values[self::CLIENT_FINISH]->serializeToString();
            $out .= $this->WriteUInt32(strlen($finish));
            $out .= $finish;
        }

        return $out;
    }
}

class ClientHello extends ProtobufMessage
{
    const EPHEMERAL = 1;
    const PAYLOAD = 3;
    const _STATIC = 2;
    protected static $fields = [
      self::EPHEMERAL => [
          'name'     => 'Ephemeral',
          'type'     => 7,
          'required' => false,
      ],
      self::PAYLOAD => [
          'name'     => 'Payload',
          'type'     => 7,
          'required' => false,
      ],
      self::_STATIC => [
          'name'     => 'Static',
          'type'     => 7,
          'required' => false,
      ],
    ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::EPHEMERAL] = null;
        $this->values[self::PAYLOAD] = null;
        $this->values[self::_STATIC] = null;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::EPHEMERAL] != null) {
            $out .= chr(10);
            $out .= $this->WriteUInt32(strlen($this->values[self::EPHEMERAL]));
            $out .= $this->values[self::EPHEMERAL];
        }
        if ($this->values[self::_STATIC] != null) {
            $out .= chr(18);
            $out .= $this->WriteUInt32(strlen($this->values[self::_STATIC]));
            $out .= $this->values[self::_STATIC];
        }
        if ($this->values[self::PAYLOAD] != null) {
            $out .= chr(26);
            $out .= $this->WriteUInt32(strlen($this->values[self::PAYLOAD]));
            $out .= $this->values[self::PAYLOAD];
        }

        return $out;
    }

    public function fields()
    {
        return self::$fields;
    }
}
class ClientFinish extends ProtobufMessage
{
    const PAYLOAD = 1;
    const _STATIC = 2;
    protected static $fields = [
      self::PAYLOAD => [
          'name'     => 'Payload',
          'type'     => 7,
          'required' => false,
      ],
      self::_STATIC => [
          'name'     => 'Static',
          'type'     => 7,
          'required' => false,
      ],
    ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::PAYLOAD] = null;
        $this->values[self::_STATIC] = null;
    }

    public function fields()
    {
        return self::$fields;
    }

    protected function WriteUInt32($val)
    {
        $result = '';
        $num1 = null;
        while (true) {
            $num1 = ($val & 127);
            $val >>= 7;
            if ($val != 0) {
                $num2 = $num1 | 128;
                $result .= chr($num2);
            } else {
                break;
            }
        }
        $result .= chr($num1);

        return $result;
    }

    public function serializeToString()
    {
        $out = '';
        if ($this->values[self::_STATIC] != null) {
            $out .= chr(10);
            $len = strlen($this->values[self::_STATIC]);
            $out .= $this->WriteUInt32($len);
            $out .= $this->values[self::_STATIC];
        }
        if ($this->values[self::PAYLOAD] != null) {
            $out .= chr(0x12);
            $len = strlen($this->values[self::PAYLOAD]);
            $out .= $this->WriteUInt32($len);
            $out .= $this->values[self::PAYLOAD];
        }

        return $out;
    }
}
class ServerHello extends ProtobufMessage
{
    const EPHEMERAL = 1;
    const PAYLOAD = 3;
    const _STATIC = 2;
    protected static $fields = [
      self::EPHEMERAL => [
          'name'     => 'Ephemeral',
          'type'     => 7,
          'required' => false,
      ],
      self::PAYLOAD => [
          'name'     => 'Payload',
          'type'     => 7,
          'required' => false,
      ],
      self::_STATIC => [
          'name'     => 'Static',
          'type'     => 7,
          'required' => false,
      ],
    ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::EPHEMERAL] = null;
        $this->values[self::PAYLOAD] = null;
        $this->values[self::_STATIC] = null;
    }

    public function fields()
    {
        return self::$fields;
    }
}
/*$a = new AudioMessage();
$a->parseFromString(hex2bin("0a6a68747470733a2f2f6d6d693234332e77686174736170702e6e65742f642f695139327648456732575267796f595736492d426b566349374e732f4172574f5a6f4d4f586b395964373761502d6e57584a324d5859473271453757332d676573654c6c6a2d2d782e656e63120a617564696f2f6d7065671a2007d11170fe30596ca17307682d2745e09862a8dcdf90c76fa90b70d381eb49732082900c280c30003a20642b56076c9b49d94cf045bf9a09033ba6391f9fbe78493e7b57365b62666627"));
echo niceVarDump($a);die();
*/
class AudioMessage extends ProtobufMessage
{
    const URL = 1; //string
    const MIMETYPE = 2; //string
    const SHA256 = 3; //string
    const LENGTH = 4; //int
    const SECONDS = 5; //int
    const PTT = 6; //bool
    const REFKEY = 7; //string
    protected static $fields = [
      self::URL => [
          'name'     => 'URL',
          'type'     => 7,
          'required' => false,
      ],
      self::MIMETYPE => [
          'name'     => 'Mimetype',
          'type'     => 7,
          'required' => false,
      ],
      self::SHA256 => [
          'name'     => 'Sha256',
          'type'     => 7,
          'required' => false,
      ],
      self::LENGTH => [
          'name'     => 'length',
          'type'     => 5,
          'required' => false,
      ],
      self::SECONDS => [
          'name'     => 'seconds',
          'type'     => 5,
          'required' => false,
      ],
      self::PTT => [
          'name'     => 'PTT',
          'type'     => 8,
          'required' => false,
      ],
      self::REFKEY => [
        'name'     => 'refkey',
        'type'     => 7,
        'required' => false,
      ],


    ];

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->values[self::URL] = null;
        $this->values[self::MIMETYPE] = null;
        $this->values[self::SHA256] = null;
        $this->values[self::LENGTH] = null;
        $this->values[self::SECONDS] = null;
        $this->values[self::PTT] = null;
        $this->values[self::REFKEY] = null;
    }

    public function setUrl($newValue)
    {
        $this->values[self::URL] = $newValue;
    }

    public function setMimeType($newValue)
    {
        $this->values[self::MIMETYPE] = $newValue;
    }

    public function setSha256($newValue)
    {
        $this->values[self::SHA256] = $newValue;
    }

    public function setLength($newValue)
    {
        $this->values[self::LENGTH] = $newValue;
    }

    public function setSeconds($newValue)
    {
        $this->values[self::SECONDS] = $newValue;
    }

    public function setRefKey($newValue)
    {
        $this->values[self::REFKEY] = $newValue;
    }

    public function setPTT($newValue)
    {
        $this->values[self::PTT] = $newValue;
    }

    public function getCaption()
    {
    }

    public function getThumbnail()
    {
    }

    public function fields()
    {
        return self::$fields;
    }
}
