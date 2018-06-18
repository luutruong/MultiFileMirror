<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\Job;

use XF\Job\AbstractJob;

class Uploader extends AbstractJob
{
    public function canTriggerByChoice()
    {
        return false;
    }

    public function run($maxRunTime)
    {
        $start = microtime(true);

        $continue = $this->resume();
        $continue->continueDate = \XF::$time + 2;

        $results = $this->app->finder('Truonglv\MultiFileMirror:MFMLink')
                ->with(['Attachment', 'Attachment.Data'])
                ->whereOr(['link', '=', ''], ['uploaded_date', '=', 0])
                ->order('created_date', 'ASC')
                ->fetch(20);

        foreach ($results as $result) {
            if ((microtime(true) - $start) >= $maxRunTime) {
                break;
            }

            $service = $this->app->service('Truonglv\MultiFileMirror:MFM', $result);

            try {
                $service->upload();
            } catch (\Exception $e) {
                // if an exception has been throw
                // the job will be stopped
            }
        }

        return $continue;
    }

    public function canCancel()
    {
        return false;
    }

    public function getStatusMessage()
    {
        return \XF::phrase('tl_mfm.upload_files');
    }
}
