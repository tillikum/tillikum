<?php
/**
 * The Tillikum Project (http://tillikum.org/)
 *
 * @link       http://tillikum.org/websvn/
 * @copyright  Copyright 2009-2012 Oregon State University (http://oregonstate.edu/)
 * @license    http://www.gnu.org/licenses/gpl-2.0-standalone.html GPLv2
 */

class Job_AttachmentController extends Tillikum_Controller_Job
{
    public function viewAction()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $id = $this->_request->getParam('id');

        $attachment = $this->getEntityManager()
            ->find(
                'Tillikum\Entity\Job\Attachment\Attachment',
                $id
            );

        if ($attachment === null) {
            $this->_response->setHttpResponseCode(404);

            return;
        }

        $attachmentData = stream_get_contents($attachment->attachment);

        $legacyFilename = str_replace(
            '"',
            '',
            iconv('UTF-8', 'ASCII//TRANSLIT', $attachment->name)
        );

        $filename = urlencode($attachment->name);

        $this->_response->setHeader(
            'Content-Type',
            $attachment->media_type
        );

        $this->_response->setHeader(
            'Content-Disposition',
            "attachment; filename=\"{$legacyFilename}\"; filename*=UTF-8''" . $filename
        );

        $this->_response->setBody($attachmentData);
    }
}
