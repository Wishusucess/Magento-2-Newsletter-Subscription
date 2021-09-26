<?php
/*
 * @Author      Hemant Singh
 * @Developer   Hemant Singh
 * @Module      Wishusucess_Newsletter
 * @copyright   Copyright (c) Wishusucess (http://www.wishusucess.com/)
 */
namespace Wishusucess\Newsletter\Controller\Subscribe;

use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Controller\Subscriber as SubscriberController;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;

class NewAction extends \Magento\Newsletter\Controller\Subscriber\NewAction {

        /**
     * @var CustomerAccountManagement
     */
    protected $customerAccountManagement;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param SubscriberFactory $subscriberFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param EmailValidator $emailValidator
     */
    public function __construct(
        Context $context,
        SubscriberFactory $subscriberFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        SubscriptionManagerInterface $subscriptionManager,
        EmailValidator $emailValidator = null
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->subscriptionManager = $subscriptionManager;
        $this->emailValidator = $emailValidator ?: ObjectManager::getInstance()->get(EmailValidator::class);
        parent::__construct(
            $context,
            $subscriberFactory,
            $customerSession,
            $storeManager,
            $customerUrl,
            $customerAccountManagement,
            $subscriptionManager,
            $emailValidator
        );
    }

        public function execute()
        {
            if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
                $gender = $this->getRequest()->getPost('gender');

                $email = (string)$this->getRequest()->getPost('email');

                try {
                    $this->validateEmailFormat($email);
                    $this->validateGuestSubscription();
                    $this->validateEmailAvailable($email);

                    $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
                    /** @var Subscriber $subscriber */
                    $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
                    if ($subscriber->getId()
                        && (int)$subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
                        throw new LocalizedException(
                            __('This email address is already subscribed.')
                        );
                    }

                    $storeId = (int)$this->_storeManager->getStore()->getId();
                    $currentCustomerId = $this->getSessionCustomerId($email);
                    $subscriber = $currentCustomerId
                        ? $this->subscriptionManager->subscribeCustomer($currentCustomerId, $storeId)
                        : $this->subscriptionManager->subscribe($email, $storeId);
                    // add gender data to the model
                    $subscriber->setData('gender',$gender);
                    $subscriber->save();
                    $message = $this->getSuccessMessage((int)$subscriber->getSubscriberStatus());
                    $this->messageManager->addSuccessMessage($message);
                } catch (LocalizedException $e) {
                    $this->messageManager->addComplexErrorMessage(
                        'localizedSubscriptionErrorMessage',
                        ['message' => $e->getMessage()]
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, __('Something went wrong with the subscription.'));
                }
            }
            /** @var Redirect $redirect */
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirectUrl = $this->_redirect->getRedirectUrl();
            return $redirect->setUrl($redirectUrl);
        }

        /**
         * Get customer id from session if he is owner of the email
         *
         * @param string $email
         * @return int|null
         */
        private function getSessionCustomerId(string $email): ?int
        {
            if (!$this->_customerSession->isLoggedIn()) {
                return null;
            }

            $customer = $this->_customerSession->getCustomerDataObject();
            if ($customer->getEmail() !== $email) {
                return null;
            }

            return (int)$this->_customerSession->getId();
        }

    
    /**
     * Get success message
     *
     * @param int $status
     * @return Phrase
     */
    private function getSuccessMessage(int $status): Phrase
    {
        if ($status === Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }

        return __('Thank you for your subscription.');
    }
    }