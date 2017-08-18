<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 16.11.2016
 * Time: 16:48
 */

namespace CustomerManagementFrameworkBundle\Helper;

use Pimcore\Model\Element\ElementInterface;

class Notes
{
    /**
     * @param ElementInterface $element
     * @param                  $type
     * @param                  $title
     * @param null $description
     *
     * @return \Pimcore\Model\Element\Note
     */
    public static function createNote(ElementInterface $element, $type, $title, $description = null)
    {
        $note = new \Pimcore\Model\Element\Note();
        $note->setElement($element);
        $note->setDate(time());
        $note->setType($type);
        $note->setTitle($title);
        $note->setDescription($description);

        return $note;
    }
}
