<?php
/**
 * @license
 * Copyright 2018 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\MultiFileMirror\XF\Entity;

use XF\Mvc\Entity\Structure;

/**
 * Class Attachment
 * @package Truonglv\MultiFileMirror\XF\Entity
 *
 * @inheritdoc
 *
 * @property \Truonglv\MultiFileMirror\Entity\MFMLink MFMLink
 */
class Attachment extends XFCP_Attachment
{
    public function MultiFileMirror_isExternalFile()
    {
        return $this->MFMLink && $this->MFMLink->link;
    }

    public function MultiFileMirror_isUploadable()
    {
        $contentType = $this->content_type;
        if (empty($contentType)) {
            return false;
        }

        $addOns = \XF::registry()->get('addOns');

        if ($contentType === 'post') {
            $allowedNodes = $this->app()->options()->tl_mfm_allowedNodes;
            if (empty($allowedNodes)) {
                return false;
            }

            /** @var \XF\Entity\Post $post */
            $post = $this->em()->find('XF:Post', $this->content_id, ['Thread']);
            if ($post && $post->Thread) {
                return in_array($post->Thread->node_id, $allowedNodes);
            }

            return false;
        } elseif (!!$this->app()->options()->tl_mfm_enableXFRM
            && strpos($contentType, 'resource') === 0
            && !empty($addOns['XFRM'])
        ) {
            $allowedCategories = $this->app()->options()->tl_mfm_allowedXFRMCategories;
            if (empty($allowedCategories)) {
                return false;
            }

            // support XenForo Resource Manager
            $category = null;
            if ($contentType === 'resource_update') {
                /** @var \XFRM\Entity\ResourceUpdate $resourceUpdate */
                $resourceUpdate = $this->em()->find(
                    'XFRM:ResourceUpdate',
                    $this->content_id,
                    ['Resource', 'Resource.Category']
                );

                if ($resourceUpdate && $resourceUpdate->Resource && $resourceUpdate->Resource->Category) {
                    $category = $resourceUpdate->Resource->Category;
                }
            } elseif ($contentType === 'resource_version') {
                /** @var \XFRM\Entity\ResourceVersion $resourceVersion */
                $resourceVersion = $this->em()->find(
                    'XFRM:ResourceVersion',
                    $this->content_id,
                    ['Resource', 'Resource.Category']
                );

                if ($resourceVersion && $resourceVersion->Resource && $resourceVersion->Resource->Category) {
                    $category = $resourceVersion->Resource->Category;
                }
            }

            if ($category && in_array($category->resource_category_id, $allowedCategories)) {
                return true;
            }

            // not allowed.
        }

        return false;
    }

    public static function getStructure(Structure $structure)
    {
        $structure = parent::getStructure($structure);

        $structure->relations['MFMLink'] = [
            'type' => self::TO_ONE,
            'entity' => 'Truonglv\MultiFileMirror:MFMLink',
            'conditions' => 'attachment_id',
            'primary' => true
        ];

        return $structure;
    }

    protected function _postSave()
    {
        parent::_postSave();

        $contentType = $this->content_type;
        $addOns = \XF::registry()->get('addOns');

        $uploadable = false;

        if ($contentType === 'post') {
            $uploadable = true;
        } elseif (!!$this->app()->options()->tl_mfm_enableXFRM
            && strpos($contentType, 'resource') === 0
            && !empty($addOns['XFRM'])
        ) {
            $uploadable = true;
        }

        if ($this->isInsert() && $uploadable) {
            /** @var \Truonglv\MultiFileMirror\Entity\MFMLink $entity */
            $entity = $this->em()->create('Truonglv\MultiFileMirror:MFMLink');
            $entity->attachment_id = $this->attachment_id;
            $entity->save();
        }
    }
}
