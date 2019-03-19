<?php

require_once __DIR__.'/../func.php';
    // Parent class
    class EventObject
    {
        protected $info;
        protected $userAgent;

        public function __construct($node, $plaintext, $userAgent = null)
        {
            $info = [];
            $this->userAgent = $userAgent;

            $this->info['is_enc'] = false;
            $this->info['node'] = $node;
            if ($node->getChild('enc') != null) {
                $waMsg = new WAMessage();
                $waMsg->parseFromString($plaintext);

                $this->info['type'] = $node->getChild('enc')->getAttribute('mediatype');
                if ($this->info['type'] == null || $this->info['type'] == '') {
                    $this->info['type'] = 'text';
                }
                // Get the specific message by type from the master protobuf
                if ($this instanceOf TextObject) {
                    $this->info['object'] = $waMsg->get(WAMessage::CONVERSATION);
                } elseif ($this instanceOf ImageObject) {
                    $this->info['object'] = $waMsg->get(WAMessage::IMAGE);
                } elseif ($this instanceOf VideoObject) {
                    $this->info['object'] = $waMsg->get(WAMessage::VIDEO);
                } elseif ($this instanceOf LocationObject) {
                    $this->info['object'] = $waMsg->get(WAMessage::LOCATION);
                } elseif ($this instanceOf UrlObject) {
                    $this->info['object'] = $waMsg->get(WaMessage::URLMSG);
                } elseif ($this instanceOf AudioObject) {
                    $this->info['object'] = $waMsg->get(WaMessage::AUDIO);
                } elseif ($this instanceOf DocumentObject) {
                    $this->info['object'] = $waMsg->get(WaMessage::DOCUMENT);
                } elseif ($this instanceOf ContactObject) {
                    $this->info['object'] = $waMsg->get(WaMessage::CONTACT);
                }

                $this->info['is_enc'] = true;
            } else {
                if ($node->getChild('media') != null) {
                    $this->info['type'] = $node->getChild('media')->getAttribute('type');
                } else {
                    $this->info['type'] = 'text';
                }
            }
        }

        /*  Shared properties between all messages */
        public function isEncrypted()
        {
            return $this->info['is_enc'];
        }

        public function getFrom()
        {
            return $this->info['node']->getAttribute('from');
        }

        public function getDate()
        {
            return $this->info['node']->getAttribute('t');
        }

        public function getMessageId()
        {
            return $this->info['node']->getAttribute('id');
        }

        public function getParticipant()
        {
            return $this->info['node']->getAttribute('participant');
        }

        public function getNode()
        {
            return $this->info['node'];
        }

        public function getNotify()
        {
            return $this->info['node']->getAttribute('notify');
        }
    }

    /*
        Location specific properties
    */
    class LocationObject extends EventObject
    {
        public function getLongitude()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('longitude');
            }

            return $this->info['object']->get(Location::LONGITUDE);
        }

        public function getLatitude()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('latitude');
            }

            return $this->info['object']->get(Location::LATITUDE);
        }

        public function getThumbnail()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getData();
            }

            return $this->info['object']->get(Location::THUMBNAIL);
        }

        public function getName()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('name');
            }

            return $this->info['object']->get(Location::NAME);
        }

        public function getUrl()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('url');
            }

            return $this->info['object']->get(Location::URL);
        }

        public function getDescription()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('name');
            }

            return $this->info['object']->get(Location::DESCRIPTION);
        }
    }
    /*
        Vcard specific properties
    */
    class ContactObject extends EventObject
    {
        public function getName()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('vcard')->getAttribute('name');
            }

            return $this->info['object']->get(ContactMessage::NAME);
        }

        public function getVCard()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('vcard')->getData();
            }

            return $this->info['object']->get(ContactMessage::VCARD);
        }
    }
    /*
        Url specific properties
    */
    class UrlObject extends EventObject
    {
        public function getMessage()
        {
            return $this->info['object']->get(MediaUrl::TEXT);
        }

        public function getUrl()
        {
            return $this->info['object']->get(MediaUrl::MATCHEDTEXT);
        }

        public function getCanonicalUrl()
        {
            return $this->info['object']->get(MediaUrl::CANONICALURL);
        }

        public function getThumbnail()
        {
            return $this->info['object']->get(MediaUrl::THUMBNAIL);
        }

        public function getTitle()
        {
            return $this->info['object']->get(MediaUrl::TITLE);
        }

        public function getDescription()
        {
            return $this->info['object']->get(MediaUrl::DESCRIPTION);
        }
    }
    class TextObject extends EventObject
    {
        public function getMessage()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('body')->getData();
            }

            return $this->info['object'];
        }
    }
    /*
        Media shared properties and methods, like Download file that Downloads and Decrypts any type of media using the key associated to the type
    */
    class MediaObject extends EventObject
    {
        const WA_DOCUMENT = 'WhatsApp Document Keys';
        const WA_AUDIO = 'WhatsApp Audio Keys';
        const WA_IMAGE = 'WhatsApp Image Keys';
        const WA_VIDEO = 'WhatsApp Video Keys';

        public function __construct($node, $plaintext, $userAgent = null)
        {
            parent::__construct($node, $plaintext, $userAgent);
            if ($this->info['is_enc']) {
                switch ($this->info['type']) {
                    case 'image':
                        $this->info['hkdf_info'] = self::WA_IMAGE;
                    break;
                    case 'audio': case 'ptt':
                        $this->info['hkdf_info'] = self::WA_AUDIO;
                    break;
                    case 'video':
                        $this->info['hkdf_info'] = self::WA_VIDEO;
                    break;
                    case 'document':
                        $this->info['hkdf_info'] = self::WA_DOCUMENT;
                    break;
                }
            }
        }

        public function DownloadFile()
        {
            $url = '';

            if ($this->info['is_enc']) {
                $class = get_class($this->info['object']);
                $url = $this->info['object']->get($class::URL);
            } else {
                $url = $this->info['node']->getChild('media')->getAttribute('url');
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return false;
            }
            if (!$this->info['is_enc']) {
                return $this->_niceDownloadFile($url);
            }

            $keys = (new HKDFv3())->deriveSecrets($this->info['object']->get($class::REFKEY), $this->info['hkdf_info'], 112);
            $iv = substr($keys, 0, 16);
            $keys = substr($keys, 16);
            $parts = str_split($keys, 32);
            $key = $parts[0];
            $macKey = $parts[1];
            $refKey = $parts[2];
            //should be changed to nice curl, no extra headers :D
            $file_enc = $this->_niceDownloadFile($url);
            //requires mac check , last 10 chars
            $mac = substr($file_enc, -10);
            $cipher_file = substr($file_enc, 0, strlen($file_enc) - 10);

            return pkcs5_unpad(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $cipher_file, MCRYPT_MODE_CBC, $iv));
        }

        protected function _niceDownloadFile($url)
        {
            //toDO: Nice curl with UserAgent
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: '.$this->userAgent, 'Content-type: identity']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            return curl_exec($ch);
        }

        public function getSHA256()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('filehash');
            }
            $class = get_class($this->info['object']);

            return base64_encode($this->info['object']->get($class::SHA256));
        }

        public function getLength()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('size');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::LENGTH);
        }

        public function getRefKey()
        {
            if (!$this->info['is_enc']) {
                return;
            }
            $class = get_class($this->info['object']);

            return bin2hex($this->info['object']->get($class::REFKEY));
        }

        public function getMimeType()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('mimetype');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::MIMETYPE);
        }

        public function getUrl()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('url');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::URL);
        }
    }
    class AudioObject extends MediaObject
    {
        public function getSeconds()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('seconds');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::SECONDS);
        }

        public function isPTT()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('origin') == 'live';
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::PTT);
        }
    }
    class ImageObject extends MediaObject
    {
        public function getWidth()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('width');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::WIDTH);
        }

        public function getHeight()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('height');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::HEIGHT);
        }

        public function getCaption()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('caption');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::CAPTION);
        }

        public function getThumbnail()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getData();
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::THUMBNAIL);
        }
    }
    class VideoObject extends MediaObject
    {
        public function getSeconds()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('seconds');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::SECONDS);
        }

        public function getCaption()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getAttribute('caption');
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::CAPTION);
        }

        public function getThumbnail()
        {
            if (!$this->info['is_enc']) {
                return $this->info['node']->getChild('media')->getData();
            }
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::THUMBNAIL);
        }
    }
    class DocumentObject extends MediaObject
    {
        public function getPages()
        {
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::PAGES);
        }

        public function getFileName()
        {
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::FILENAME);
        }

        public function getThumbnail()
        {
            $class = get_class($this->info['object']);

            return $this->info['object']->get($class::THUMBNAIL);
        }
    }
