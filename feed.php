<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class feed extends Module
{
    public function __construct()
    {
        $this->name = 'feed';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'João Victor';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => '1.7.99',
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('feed');
        $this->description = $this->l('Teste.');

        $this->confirmUninstall = $this->l('Tem certeza que deseja desinstalar?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('nome não encontrado');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

    return (
            parent::install() 
            && $this->registerHook('dashboardZoneTwo')
            && Configuration::updateValue('MYMODULE_NAME', 'feed')
        ); 
    }

    public function hookdashboardZoneTwo($params)
    {
        $this->context->smarty->assign(
            array(
                "list" => $this->getRss()
            )
        );
        return $this->display(__FILE__, 'dashboard_zone_one.tpl');
    }


    public function uninstall()
    {
        return (
            parent::uninstall() 
            && Configuration::deleteByName('MYMODULE_NAME')
        );
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $configValue = (string) Tools::getValue('URL_FEED');

            if (empty($configValue)) {
                $output = $this->displayError($this->l('URL invalida'));
            } else {
                Configuration::updateValue('URL_FEED', $configValue);
                $output = $this->displayConfirmation($this->l('URL atualizada'));
            }
        }
        return $output . $this->displayForm();
    }

   /**  * Builds the configuration form  * @return string HTML code  */
    public function displayForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('URL do RSS'),
                        'name' => 'URL_FEED',
                        'size' => 20,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;

        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->fields_value['URL_FEED'] = Tools::getValue('URL_FEED', Configuration::get('URL_FEED'));

        return $helper->generateForm([$form]);
    }

    public function getRss()
    {
        $url=Configuration::get('URL_FEED');
        $obj=new DOMDocument();
        $obj->load($url);
        $content=$obj->getElementsByTagName('item');
        $list=[];
        $limit=5;
        foreach ($content as $key => $row) {
            $topic=[];
            $topic['link']=isset($row->getElementsByTagName("link")->item(0)->nodeValue) ? $row->getElementsByTagName("link")->item(0)->nodeValue : '' ;
            $topic['title']=isset($row->getElementsByTagName("title")->item(0)->nodeValue) ? $row->getElementsByTagName("title")->item(0)->nodeValue : '' ;
            $topic['pubDate']=isset($row->getElementsByTagName("pubDate")->item(0)->nodeValue) ? date_format(date_create($row->getElementsByTagName("pubDate")->item(0)->nodeValue),"d/m/Y H:i") : '' ;
            $topic['description']=isset($row->getElementsByTagName("description")->item(0)->nodeValue) ? $row->getElementsByTagName("description")->item(0)->nodeValue : '' ;
            $topic['author']=isset($row->getElementsByTagName("author")->item(0)->nodeValue) ? $row->getElementsByTagName("author")->item(0)->nodeValue : '' ;
            array_push($list,$topic);
            if($key == $limit) break;
        }
        return $list;
    }

}