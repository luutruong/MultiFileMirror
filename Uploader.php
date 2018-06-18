<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror;

use XF\Util\Random;
use Truonglv\MultiFileMirror\Entity\MFMLink;

class Uploader
{
    const UPLOAD_TEMPLATE = 'https://www.multifilemirror.com/uapi/%s/?url=%s&sites=%s&filename=%s';
    const TOKEN_INPUT_NAME = 't';

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var array
     */
    protected $sites;

    /**
     * @var array
     */
    protected $allowedExtensions;

    /**
     * @var \XF\Entity\Attachment|null
     */
    protected $attachment;

    /**
     * @var \XF\App
     */
    protected $app;

    protected $link;

    public function __construct(MFMLink $link)
    {
        $this->link = $link;

        $this->setupDefaults();
    }

    public function setSites(array $target)
    {
        $this->sites = $target;
    }

    public function setApiKey($key)
    {
        $this->apiKey = $key;
    }

    public function setAllowedExtensions(array $allowed)
    {
        $this->allowedExtensions = $allowed;
    }

    /**
     * @return bool|string
     */
    public function upload()
    {
        if ($this->isMismatchOptions()) {
            return false;
        }
        
        $mfmLink = $this->link;
        $now = time();

        /** @var \Truonglv\MultiFileMirror\XF\Entity\Attachment $attachment */
        $attachment = $mfmLink->Attachment;

        if (empty($attachment)) {
            $mfmLink->uploaded_date = $now;
            $mfmLink->save();

            return;
        }

        if (empty($attachment->content_id) || empty($attachment->content_type)) {
            // leave to retry later.
            return;
        }

        if (!$attachment->MultiFileMirror_isUploadable()) {
            // need delete the record.
            $mfmLink->delete();

            return;
        }

        if (!in_array($attachment->getExtension(), $this->allowedExtensions)) {
            return false;
        }

        if (empty($mfmLink->token)) {
            $mfmLink->token = md5(Random::getRandomString(32) . microtime(true));
            $mfmLink->save();
        }

        $downloadLink = $this->app->router('public')
                                  ->buildLink(
                                      'full:attachments/mfm-raw',
                                      $attachment,
                                      [
                                          self::TOKEN_INPUT_NAME => $mfmLink->token,
                                          'hash' => $attachment->temp_hash ?: null
                                      ]
                                  );

        $targetUrl = sprintf(
            self::UPLOAD_TEMPLATE,
            $this->apiKey,
            urlencode($downloadLink),
            urlencode(implode(',', $this->sites)),
            urlencode($attachment->filename)
        );

        $response = $this->client->get($targetUrl);
        $body = $response->getBody()->getContents();

        $body = trim($body);
        if (empty($body)) {
            return;
        }

        if (strpos($body, 'http://') === 0
            || strpos($body, 'https://') === 0
        ) {
            $mfmLink->link = $body;
            $mfmLink->uploaded_date = $now;
            $mfmLink->save();
        }
    }

    protected function isMismatchOptions()
    {
        return empty($this->apiKey) || empty($this->allowedExtensions) || empty($this->sites);
    }

    protected function setupDefaults()
    {
        $this->app = \XF::app();
        $this->client = $this->app->http()->createClient();

        $this->setSites(array_keys($this->app->options()->tl_mfm_targetHosts));
        $this->setApiKey($this->app->options()->tl_mfm_apiKey);

        $allowedExtensions = $this->app->options()->tl_mfm_allowedExtensions;
        $allowedExtensions = preg_split("/(\n|\r\n)/", $allowedExtensions);
        $allowedExtensions = array_map('trim', $allowedExtensions);

        $this->setAllowedExtensions($allowedExtensions);
    }
}
