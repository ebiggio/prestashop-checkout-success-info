<?php
/**
 * Checkout Success Info
 *
 * Shows additional information on the order confirmation page.
 *
 * @author Enzo Biggio <ebiggio@gmail.com>
 * @version 1.0.0
 */

if (!defined('_PS_VERSION_')) exit;

class CheckoutSuccessInfo extends Module
{
    protected $_html = '';

    public function __construct()
    {
        $this->name = 'checkoutsuccessinfo';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Enzo Biggio';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        $this->displayName = $this->l('Checkout Success Info');
        $this->description = $this->l('Muestra información adicional en la página de confirmación de compra.');
        $this->confirmUninstall = $this->l('¿Estás seguro de querer desinstalar este módulo?');

        parent::__construct();
    }

    public function install(): bool
    {
        Configuration::updateValue('CHECKOUTSUCCESSINFO_HTML', '');

        return
            parent::install()
            && $this->registerHook('displayOrderConfirmation1');
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName('CHECKOUTSUCCESSINFO_HTML');

        return
            parent::uninstall()
            && $this->unregisterHook('displayOrderConfirmation1');
    }

    public function getContent()
    {
        if ((Tools::isSubmit('submitCheckoutSuccessInfoModule'))) {
            Configuration::updateValue('CHECKOUTSUCCESSINFO_HTML', Tools::getValue('CHECKOUTSUCCESSINFO_HTML'));

            $this->_html .= $this->displayConfirmation($this->l('Configuración actualizada'));
        }

        return $this->_html . $this->renderForm();
    }

    public function renderForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCheckoutSuccessInfoModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module='
            . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array(
                'CHECKOUTSUCCESSINFO_HTML' => Configuration::get('CHECKOUTSUCCESSINFO_HTML'),
            ),
        );

        return $helper->generateForm($this->getConfigForm());
    }

    public function getConfigForm()
    {
        $configuration_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Configuración'),
                    'icon'  => 'icon-cogs',
                ),
                'input'  => array(
                    array(
                        'type'         => 'textarea',
                        'label'        => $this->l('Contenido a mostrar'),
                        'name'         => 'CHECKOUTSUCCESSINFO_HTML',
                        'desc'         => $this->l('Puedes utilizar HTML para personalizar el contenido.'),
                        'autoload_rte' => true,
                        'required'     => false
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Guardar'),
                )
            )
        );

        return array($configuration_form);
    }

    /**
     * Show the module content on the order confirmation page
     *
     * @param $params
     * @return bool|string
     */
    public function hookDisplayOrderConfirmation1($params)
    {
        $html = Configuration::get('CHECKOUTSUCCESSINFO_HTML');

        if (empty($html)) {
            return false;
        }

        $this->context->smarty->assign([
            'checkoutSuccessInfo' => $html,
        ]);

        return $this->display(__FILE__, 'checkoutsuccessinfo.tpl');
    }
}
