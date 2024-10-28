<?php
/**
 * Contact
 *
 * @author Slava Yurthev
 */
namespace SY\Contact\Block;

use Magento\Contact\Block\ContactForm as BaseContactForm;
use Magento\Customer\Model\Session;
use Magento\Email\Model\Template\FilterFactory;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use SY\Contact\Helper\Data;

class ContactForm extends BaseContactForm
{

    private $_fields;

    public function __construct(
        Context $context,
        protected readonly Session $_customerSession,
        protected readonly FormKey $formKey,
        private readonly FilterFactory $templateFilter,
        private readonly Data $helper,
        private readonly Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getJsFormConfig()
    {
        return json_encode([
            "validation" => new \stdClass(),
            "customContactForm" => $this->getCustomContactFormData(),
        ]);
    }

    protected function getCustomContactFormData()
    {
        $fields = $this->getFields();
        $result = [];
        foreach ($fields as $field) {
            $result[$field->getKey()] = [
                'show_if' => $field->getShowIf(),
            ];

        }

        return $result;
    }

    public function getFields()
    {
        if (!$this->_fields) {
            $fields = $this->helper->getContactConfig(
                'general/fields',
                $this->_storeManager->getStore()->getId()
            );
            $fields = $this->json->unserialize($fields);
            if (count($fields) > 0) {
                foreach ($fields as $key => $field) {
                    $object = new \Magento\Framework\DataObject;
                    $field['default_value'] = $this->runDirectives($field['default_value']);
                    $object->addData($field);
                    $fields[$key] = $object;
                }
            }
            $this->_fields = $fields;
        }

        return $this->_fields;
    }

    /**
     * Runs email template directives on the value, enabling us to use default values like
     * {{customer.firstname}}
     * {{customerDefaultBilling.city}}
     * and such
     *
     * @param $value
     */
    protected function runDirectives($value)
    {
        $filter = $this->templateFilter->create([
            'variables' => $this->collectVariables(),
        ]);

        return $filter->filter($value);
    }

    protected function collectVariables()
    {
        $customer = $this->_customerSession->getCustomer();

        $customerDefaultBilling = $customer->getDefaultBillingAddress();
        $customerDefaultShipping = $customer->getDefaultShippingAddress();

        return compact('customer', 'customerDefaultBilling', 'customerDefaultShipping');
    }
}
