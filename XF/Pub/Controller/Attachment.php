<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\XF\Pub\Controller;

use XF\Mvc\Reply\View;
use XF\Mvc\ParameterBag;
use Truonglv\MultiFileMirror\Uploader;

class Attachment extends XFCP_Attachment
{
    public function actionIndex(ParameterBag $params)
    {
        $response = parent::actionIndex($params);
        if ($response instanceof View) {
            /** @var \Truonglv\MultiFileMirror\XF\Entity\Attachment $attachment */
            $attachment = $response->getParam('attachment');
            if ($attachment && $attachment->MultiFileMirror_isExternalFile()) {
                return $this->redirect($attachment->MFMLink->link);
            }
        }

        return $response;
    }

    public function actionMFMRaw(ParameterBag $params)
    {
        $GLOBALS['tl_MFMLink_alwaysUseFileAttachment'] = true;

        /** @var \Truonglv\MultiFileMirror\XF\Entity\Attachment $attachment */
        $attachment = $this->em()->find('XF:Attachment', $params->attachment_id, ['MFMLink']);
        if (!$attachment) {
            throw $this->exception($this->notFound());
        }
        
        if ($attachment->temp_hash) {
            $hash = $this->filter('hash', 'str');
            if ($attachment->temp_hash !== $hash) {
                return $this->noPermission();
            }
        }

        $token = $this->filter(Uploader::TOKEN_INPUT_NAME, 'str');

        $entity = $attachment->MFMLink;
        $maxViews = (int) $this->options()->tl_mfm_maxViewMFMRaw;

        if (empty($entity)
            || !$entity->token
            || $entity->token !== $token
            || $entity->view_count >= $maxViews
        ) {
            return $this->noPermission();
        }

        $entity->view_count++;
        $entity->save();

        /** @var \XF\ControllerPlugin\Attachment $attachPlugin */
        $attachPlugin = $this->plugin('XF:Attachment');

        return $attachPlugin->displayAttachment($attachment);
    }
}
