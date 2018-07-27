<?php
/**
 * Created by PhpStorm.
 * User: tmittendorfer
 * Date: 13.07.2018
 * Time: 09:34
 */

namespace CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\Newsletter2Go;


use CustomerManagementFrameworkBundle\Newsletter\ProviderHandler\NewsletterTemplateExporterInterface;
use Pimcore\Model\Document;



class TemplateExporter implements NewsletterTemplateExporterInterface
{

    protected $newsletter2GoRESTApi;

    public function __construct(\NL2GO\Newsletter2Go_REST_Api $newsletter2GoRESTApi)
    {
        $this->newsletter2GoRESTApi = $newsletter2GoRESTApi;
    }


    /**
     * for placeholders refer to: https://hilfe.newsletter2go.com/newsletter-erstellen/personalisierung/wie-kann-ich-merkmale-uber-platzhalter-im-newsletter-ausgeben-und-individuell-anpassen.html
     *
     *
     * @param Document\Newsletter $document
     * @throws \Exception
     */
    public function exportTemplate(Document\Newsletter $document)
    {
        $html = \Pimcore\Model\Document\Service::render($document);
        // modifying the content e.g set absolute urls...
        $html = \Pimcore\Helper\Mail::embedAndModifyCss($html, $document);
        $html = \Pimcore\Helper\Mail::setAbsolutePaths($html, $document);



        $response = $this->newsletter2GoRESTApi->createNewsletter('7ajpme6f', 'default', $document->getKey(), $document->getFrom(), $document->getSubject(), $html);


        //TODO...
        var_dump($response);
    }
}