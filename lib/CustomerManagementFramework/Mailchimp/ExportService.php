<?php

namespace CustomerManagementFramework\Mailchimp;

use Carbon\Carbon;
use CustomerManagementFramework\Model\CustomerInterface;
use DrewM\MailChimp\MailChimp;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\Note;
use Pimcore\Model\Object\Concrete;

class ExportService
{
    const NOTE_TYPE = 'export.mailchimp';

    /**
     * @var MailChimp
     */
    protected $apiClient;

    /**
     * @var string
     */
    protected $listId;

    /**
     * @var Note[][]
     */
    protected $notes = [];

    /**
     * @param MailChimp $apiClient
     * @param $listId
     */
    public function __construct(MailChimp $apiClient, $listId)
    {
        $this->apiClient = $apiClient;
        $this->listId    = $listId;
    }

    /**
     * @return MailChimp
     */
    public function getApiClient()
    {
        return $this->apiClient;
    }

    /**
     * @return string
     */
    public function getListId()
    {
        return $this->listId;
    }

    /**
     * @param array $entry
     * @return array|false
     */
    public function create(array $entry)
    {
        return $this->apiClient->post(
            sprintf('lists/%s/members', $this->listId),
            $entry
        );
    }

    /**
     * @param array $entry
     * @return array|false
     */
    public function update(array $entry)
    {
        return $this->apiClient->put(
            sprintf('lists/%s/members/%s', $this->listId, $this->getSubscriberId($entry['email_address'])),
            $entry
        );
    }

    /**
     * @param string $email
     * @return string
     */
    public function getSubscriberId($email)
    {
        return md5(strtolower($email));
    }

    /**
     * @param CustomerInterface $customer
     * @return \DateTime|null
     */
    public function getLastExportDateTime(CustomerInterface $customer)
    {
        $notes = $this->getExportNotes($customer);
        if ($notes) {
            return $this->getNoteDateTime($notes[0]);
        }
    }

    /**
     * @param CustomerInterface $customer
     * @return bool
     */
    public function wasCreated(CustomerInterface $customer)
    {
        return null !== $this->getLastExportDateTime($customer);
    }

    /**
     * @param CustomerInterface|Concrete $customer
     * @return bool
     */
    public function needsUpdate(CustomerInterface $customer)
    {
        // no last export -> needs update
        if (!$this->wasCreated($customer)) {
            return true;
        }

        if ($customer->getModificationDate()) {
            $lastExportDate   = $this->getLastExportDateTime($customer);
            $modificationDate = Carbon::createFromTimestamp($customer->getModificationDate(), $lastExportDate->getTimezone());

            // item was modified after last export -> needs update
            if ($modificationDate > $lastExportDate) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CustomerInterface|ElementInterface $customer
     * @param \DateTime|null $date
     * @return Note
     */
    public function createExportNote(CustomerInterface $customer, \DateTime $date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }

        $note = new Note();
        $note->setElement($customer);
        $note->setDate($date->getTimestamp());
        $note->setType(static::NOTE_TYPE);
        $note->setTitle('Mailchimp Export');

        return $note;
    }

    /**
     * @param CustomerInterface $customer
     * @return Note\Listing|\Pimcore\Model\Object\Listing\Dao|Note[]
     */
    public function getExportNotes(CustomerInterface $customer, $refresh = false)
    {
        if (!isset($this->notes[$customer->getId()]) || $refresh) {
            /** @var Note\Listing|\Pimcore\Model\Object\Listing\Dao $list */
            $list = new Note\Listing();
            $list->setOrderKey('date');
            $list->setOrder('desc');
            $list->addConditionParam('type = ?', static::NOTE_TYPE);
            $list->addConditionParam('cid = ?', $customer->getId());

            $this->notes[$customer->getId()] = $list->load();
        }

        return $this->notes[$customer->getId()];
    }

    /**
     * @param Note $note
     * @return \DateTime
     */
    protected function getNoteDateTime(Note $note)
    {
        return Carbon::createFromTimestamp($note->getDate());
    }
}
