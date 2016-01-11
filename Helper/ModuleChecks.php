<?php

namespace ClassyLlama\AvaTax\Helper;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\TaxRuleRepositoryInterface;

/**
 * Class ModuleChecks
 */
class ModuleChecks
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * ModuleChecks constructor
     *
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        TaxRuleRepositoryInterface $taxRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get module check errors
     *
     * @return array
     */
    public function getModuleCheckErrors()
    {
        $errors = array();
        $errors = array_merge(
            $errors,
            $this->checkNativeTaxRules(),
            $this->checkSoapSupport(),
            $this->checkSslSupport()
        );

        return $errors;
    }

    /**
     * Check to see if there are any native tax rules created that may affect AvaTax
     *
     * @return array
     */
    protected function checkNativeTaxRules()
    {
        $errors = [];
        $taxRules = $this->taxRuleRepository->getList($this->searchCriteriaBuilder->create());
        if (count($taxRules->getItems())) {
            $errors[] = __(
                'You have %1 native Magento Tax Rule(s) configured. '
                    . 'Please <a href="%2">review them</a> and delete any that you do not specifically want enabled. '
                    . 'You should only have rules setup if you want to use them as backup rules in case of AvaTax '
                    . 'errors (see <a href="#row_tax_avatax_error_handling_header">Error Action setting</a>) '
                    . 'or if you need to support VAT tax.',
                count($taxRules->getItems()),
                $this->urlBuilder->getUrl('tax/rule')
            );
        }
        return $errors;
    }

    /**
     * Check SOAP support
     *
     * @return array
     */
    protected function checkSoapSupport()
    {
        $errors = [];
        if (!class_exists('SoapClient')) {
            $errors[] = __(
                'The PHP class SoapClient is missing. It must be enabled to use this extension. See %1 for details.',
                '<a href="http://www.php.net/manual/en/book.soap.php" target="_blank">http://www.php.net/manual/en/book.soap.php</a>'
            );
        }

        return $errors;
    }

    /**
     * Check SSL support
     *
     * @return array
     */
    protected function checkSslSupport()
    {
        $errors = [];
        if (!function_exists('openssl_sign')) {
            $errors[] = __(
                'SSL must be enabled in PHP to use this extension. Typically, OpenSSL is used but it is not enabled on your server. This may not be a problem if you have some other form of SSL in place. For more information about OpenSSL, see %1.',
                '<a href="http://www.php.net/manual/en/book.openssl.php" target="_blank">http://www.php.net/manual/en/book.openssl.php</a>'
            );
        }

        return $errors;
    }
}