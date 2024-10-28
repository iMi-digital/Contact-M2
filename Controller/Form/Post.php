<?php
/**
 * Contact
 *
 * @author Slava Yurthev
 */
namespace SY\Contact\Controller\Form;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use SY\Contact\Helper\Data;
use SY\Contact\Helper\Email;
use SY\Contact\Model\Request;

class Post extends Action {

    public function __construct(
        Context $context,
        private readonly Request $request,
        private readonly Data $helper,
        private readonly Json $json,
        private readonly Validator $validator,
        private readonly StoreManagerInterface $storeManager,
        private readonly Email $email,
        private readonly LoggerInterface $logger
    ){
        parent::__construct($context);
    }

    public function execute() {
		$validator = $this->validator;
		if ($validator->validate($this->getRequest())) {
			$store = $this->storeManager->getStore();
			$fields = $this->helper->getContactConfig('general/fields', $store->getId());
			$fields = $this->json->unserialize($fields);
			$info = [];
			if(count($fields)>0){
				foreach ($fields as $field) {
					try {
						if($this->getRequest()->getParam($field['key'])){
							$info[] = [
								'key' => $field['key'],
								'label' => $field['label'],
								'type' => $field['field_type'],
								'value' => $this->getRequest()->getParam($field['key'])
							];
						}
					} catch (\Exception $e) {}
				}
			}
			if(count($info)>0){
				$model = $this->request;
				$model->setData('info', $this->json->serialize($info));
                //used to support hidden fields
                $model->setData('originalParams', $this->getRequest()->getParams());
				try {
					$model->save();
					if($model->getId()){
						$email = $this->email;
						$email->receive($model, $store->getId());
                        $this->messageManager->addSuccess(__('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.'));
					}
                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage(), ['exception' => $e]);
                    $this->messageManager->addError(__("We're sorry, an error has occurred while generating this email."));
                }
			}
		}
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
		return $resultRedirect;
	}
}
