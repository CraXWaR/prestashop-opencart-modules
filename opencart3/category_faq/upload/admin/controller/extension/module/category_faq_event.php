<?php
class ControllerExtensionModuleCategoryFaqEvent extends Controller
{
    public function onAfterEditCategory(&$route, &$args, &$output)
    {
        $this->load->model('extension/module/category_faq');

        $category_id = (int) $args[0];

        $this->model_extension_module_category_faq->saveFaqs($category_id, $this->request->post['category_faq'] ?? []);
    }

    public function onBeforeEditCategory(&$route, &$data, &$output)
    {
        $this->load->model('extension/module/category_faq');

        $data['category_faqs'] = $this->model_extension_module_category_faq->getFaqs((int) ($this->request->get['category_id'] ?? 0));
    }

    public function onAfterDeleteCategory(&$route, &$args, &$output)
    {
        $this->load->model('extension/module/category_faq');

        $this->model_extension_module_category_faq->deleteFaqsByCategoryId((int) $args[0]);
    }

}