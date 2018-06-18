<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Class MFMLink
 * @package Truonglv\MultiFileMirror\Entity
 *
 * @property int attachment_id
 * @property string link
 * @property int created_date
 * @property string token
 * @property int view_count
 * @property int uploaded_date
 * @property bool is_attach_removed
 *
 * @property \XF\Entity\Attachment Attachment
 */
class MFMLink extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'tl_mfm_link';
        $structure->shortName = 'Truonglv\MultiFileMirror:MFMLink';
        $structure->primaryKey = 'attachment_id';
        $structure->columns = [
            'attachment_id' => ['type' => self::UINT, 'required' => true],
            'link' => ['type' => self::STR, 'default' => ''],
            'token' => ['type' => self::STR, 'maxLength' => 32, 'default' => ''],
            'view_count' => ['type' => self::UINT, 'default' => 0],
            'created_date' => ['type' => self::UINT, 'default' => time()],
            'uploaded_date' => ['type' => self::UINT, 'default' => 0],
            'is_attach_removed' => ['type' => self::BOOL, 'default' => false]
        ];

        $structure->relations = [
            'Attachment' => [
                'type' => self::TO_ONE,
                'entity' => 'XF:Attachment',
                'conditions' => 'attachment_id',
                'primary' => true
            ]
        ];

        return $structure;
    }
}
