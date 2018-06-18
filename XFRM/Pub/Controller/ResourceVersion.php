<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\XFRM\Pub\Controller;

use XF\Mvc\Reply\View;
use XF\Mvc\ParameterBag;

class ResourceVersion extends XFCP_ResourceVersion
{
    public function actionDownload(ParameterBag $params)
    {
        $response = parent::actionDownload($params);
        if ($response instanceof View) {
            /** @var \Truonglv\MultiFileMirror\XF\Entity\Attachment $attachment */
            $attachment = $response->getParam('attachment');
            if ($attachment && $attachment->MultiFileMirror_isExternalFile()) {
                return $this->redirect($attachment->MFMLink->link);
            }
        }
        
        return $response;
    }
}
