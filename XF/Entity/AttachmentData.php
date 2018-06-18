<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\XF\Entity;

use XF\Mvc\Entity\Structure;

class AttachmentData extends XFCP_AttachmentData
{
    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        if (!isset($structure->relations['Attachment'])) {
            $structure->relations['Attachment'] = [
                'type' => self::TO_ONE,
                'entity' => 'XF:Attachment',
                'conditions' => [
                    ['data_id', '=', '$data_id']
                ],
                'primary' => true
            ];
        }

        return $structure;
    }
    
    public function isDataAvailable()
    {
        if (isset($GLOBALS['tl_MFMLink_alwaysUseFileAttachment'])) {
            return parent::isDataAvailable();
        }

        if ($this->Attachment
            && $this->Attachment->MFMLink
            && $this->Attachment->MFMLink->is_attach_removed
        ) {
            return true;
        }

        return parent::isDataAvailable();
    }
}
