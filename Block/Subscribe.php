<?php
/*
 * @Author      Hemant Singh
 * @Developer   Hemant Singh
 * @Module      Wishusucess_Newsletter
 * @copyright   Copyright (c) Wishusucess (http://www.wishusucess.com/)
 */
namespace Wishusucess\Newsletter\Block;
use Magento\Framework\View\Element\Template;

class Subscribe extends \Magento\Framework\View\Element\Template
{
    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('newsletter/subscriber/new', ['_secure' => true]);
    }
}



/*class Subscribe extends Template {
  public function __construct(Template\Context $context,array $data = []) {
        parent::__construct($context, $data);
    }
    public function beforeToHtml(\Magento\Newsletter\Block\Subscribe $originalBlock){
        $originalBlock->setTemplate('BDC_Newsletter::subscribe.phtml');
    }
 }*/
