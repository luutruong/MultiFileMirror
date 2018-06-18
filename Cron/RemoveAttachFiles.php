<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\Cron;

use XF\Util\File;

class RemoveAttachFiles
{
    public static function runHourly()
    {
        $removeFileCutOff = (int) \XF::app()->options()->tl_mfm_removeAttachFiles;
        if (empty($removeFileCutOff)) {
            return;
        }

        $cutOff = \XF::$time - $removeFileCutOff * 86400;

        $results = \XF::finder('Truonglv\MultiFileMirror:MFMLink')
                ->with(['Attachment', 'Attachment.Data'])
                ->where('is_attach_removed', 0)
                ->where('link', '<>', '')
                ->where('view_count', '>', 0)
                ->where('uploaded_date', '<=', $cutOff)
                ->order('uploaded_date', 'ASC')
                ->limit(20)
                ->fetch();

        /** @var \Truonglv\MultiFileMirror\Entity\MFMLink $result */
        foreach ($results as $result) {
            if (!$result->Attachment || !$result->Attachment->Data) {
                $result->is_attach_removed = true;
                $result->save();

                continue;
            }

            $abstractedPath = $result->Attachment->Data->getAbstractedDataPath();
            File::deleteFromAbstractedPath($abstractedPath);

            $result->is_attach_removed = true;
            $result->save();
        }
    }
}
