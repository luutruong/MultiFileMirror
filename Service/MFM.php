<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\Service;

use XF\Service\AbstractService;
use Truonglv\MultiFileMirror\Uploader;
use Truonglv\MultiFileMirror\Entity\MFMLink;

class MFM extends AbstractService
{
    protected $mfmLink;

    public function __construct(\XF\App $app, MFMLink $mfmLink)
    {
        parent::__construct($app);

        $this->mfmLink = $mfmLink;
    }

    public function upload()
    {
        $mfmLink = $this->mfmLink;

        $uploader = new Uploader($mfmLink);
        $uploader->upload();
    }
}
