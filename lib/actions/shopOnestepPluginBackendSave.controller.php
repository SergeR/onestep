<?php

class shopOnestepPluginBackendSaveController extends waJsonController {

    protected $templates = array(
        'onestep' => array('name' => 'Главный шаблон', 'tpl_path' => 'plugins/onestep/templates/onestep.html', 'public' => false),
        'checkout' => array('name' => 'Шаблон оформления заказа (checkout.html)', 'tpl_path' => 'plugins/onestep/templates/checkout.html', 'public' => false),
        'cart_js' => array('name' => 'cart.js', 'tpl_path' => 'plugins/onestep/js/cart.js', 'public' => true),
        
        'checkout.contactinfo' => array('name' => 'Шаблон оформления заказа - Контактная информация (checkout.contactinfo.html)', 'tpl_path' => 'plugins/onestep/templates/checkout.contactinfo.html', 'public' => false),
        'checkout.shipping' => array('name' => 'Шаблон оформления заказа - Доставка (checkout.shipping.html)', 'tpl_path' => 'plugins/onestep/templates/checkout.shipping.html', 'public' => false),
        'checkout.payment' => array('name' => 'Шаблон оформления заказа - Оплата (checkout.payment.html)', 'tpl_path' => 'plugins/onestep/templates/checkout.payment.html', 'public' => false),
        'checkout.confirmation' => array('name' => 'Шаблон оформления заказа - Подтверждение (checkout.confirmation.html)', 'tpl_path' => 'plugins/onestep/templates/checkout.confirmation.html', 'public' => false), 
    );
    protected $plugin_id = array('shop', 'onestep');

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $settings = waRequest::post('shop_onestep', array());

            foreach ($settings as $name => $value) {
                $app_settings_model->set($this->plugin_id, $name, $value);
            }

            $post_templates = waRequest::post('templates');
            $reset_tpls = waRequest::post('reset_tpls');

            foreach ($this->templates as $id => $template) {
                if (isset($reset_tpls[$id])) {
                    $template_path = wa()->getDataPath($template['tpl_path'], $template['public'], 'shop', true);
                    @unlink($template_path);
                } else {

                    if (!isset($post_templates[$id])) {
                        throw new waException('Не определён шаблон');
                    }
                    $post_template = $post_templates[$id];

                    $template_path = wa()->getDataPath($template['tpl_path'], $template['public'], 'shop', true);
                    if (!file_exists($template_path)) {
                        $template_path = wa()->getAppPath($template['tpl_path'], 'shop');
                    }

                    $template_content = file_get_contents($template_path);                 
                    if ($template_content != $post_template) {
                        $template_path = wa()->getDataPath($template['tpl_path'], $template['public'], 'shop', true);

                        $f = fopen($template_path, 'w');
                        if (!$f) {
                            throw new waException('Не удаётся сохранить шаблон. Проверьте права на запись ' . $template_path);
                        }
                        fwrite($f, $post_template);
                        fclose($f);
                    }
                }
            }

            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
