<?php
/*
 * @Author      Hemant Singh
 * @Developer   Hemant Singh
 * @Module      Wishusucess_Newsletter
 * @copyright   Copyright (c) Wishusucess (http://www.wishusucess.com/)
 */
namespace Wishusucess\Newsletter\Model;

class Subscriber extends \Magento\Newsletter\Model\Subscriber {

    /**
     * Initialize resource model
     *
     * @return void
     */

    public function subscribe($email) {
        echo "=----------->";exit;

        $this->loadByEmail($email);

        if (!$this->getId()) {
            $this->setSubscriberConfirmCode($this->randomSequence());
        }

        $isConfirmNeed = $this->_scopeConfig->getValue(
                        self::XML_PATH_CONFIRMATION_FLAG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) == 1 ? true : false;
        $isOwnSubscribes = false;

        $isSubscribeOwnEmail = $this->_customerSession->isLoggedIn() && $this->_customerSession->getCustomerDataObject()->getEmail() == $email;

        if (!$this->getId() || $this->getStatus() == self::STATUS_UNSUBSCRIBED || $this->getStatus() == self::STATUS_NOT_ACTIVE
        ) {
            if ($isConfirmNeed === true) {
                // if user subscribes own login email - confirmation is not needed
                $isOwnSubscribes = $isSubscribeOwnEmail;
                if ($isOwnSubscribes == true) {
                    $this->setStatus(self::STATUS_SUBSCRIBED);
                } else {
                    $this->setStatus(self::STATUS_NOT_ACTIVE);
                }
            } else {
                $this->setStatus(self::STATUS_SUBSCRIBED);
            }

            $this->setSubscriberEmail($_POST['email']);

        }

        if(!empty($_POST['subscriber_name']) ){
                $this->setSubscriberName($_POST['subscriber_name']); //subscriber_name
                $this->setSubscriberDateofbirth($_POST['subscriber_dateofbirth']); //date of birth
                $this->setSubscriberCountrycode($_POST['subscriber_countrycode']); //country code
        }

        if ($isSubscribeOwnEmail) {
            try {
                $customer = $this->customerRepository->getById($this->_customerSession->getCustomerId());
                $this->setStoreId($customer->getStoreId());
                $this->setCustomerId($customer->getId());
            } catch (NoSuchEntityException $e) {
                $this->setStoreId($this->_storeManager->getStore()->getId());
                $this->setCustomerId(0);
            }
        } else {
            $this->setStoreId($this->_storeManager->getStore()->getId());
            $this->setCustomerId(0);
        }

        $this->setStatusChanged(true);

        try {
            $this->save();
            if ($isConfirmNeed === true && $isOwnSubscribes === false
            ) {
                $this->sendConfirmationRequestEmail();
            } else {
                $this->sendConfirmationSuccessEmail();
            }
            return $this->getStatus();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

}