<?php

class ControllerExtensionModuleCategoryFaqEvent extends Controller
{
    public function onCategoryPageBefore(&$route, &$data, &$output)
    {
        $status = $this->config->get('module_category_faq_status');
        $path = $this->request->get['path'] ?? '';
        $parts = explode('_', $path);
        $category_id = (int) end($parts);

        if (!$status) {
            return;
        }

        if (!$category_id) {
            return;
        }

        $page = (int) ($this->request->get['page'] ?? 1);

        if ($page > 1) {
            return;
        }

        $this->load->model('extension/module/category_faq');
        $faqs = $this->model_extension_module_category_faq->getCategoryFaqs($category_id);

        if (!$faqs) {
            return;
        }

        $data['faqs'] = $faqs;

        $head_inject = $this->buildFaqSchema($faqs) . $this->buildFaqStyle() . $this->buildPaginationToggle();

        if ($head_inject !== '' && isset($data['header']) && is_string($data['header'])) {
            $pos = stripos($data['header'], '</head>');

            if ($pos !== false) {
                $data['header'] = substr($data['header'], 0, $pos) . $head_inject . substr($data['header'], $pos);
            }
        }
    }

    private function buildPaginationToggle()
    {
        $js = <<<'JS'
(function () {
    function faqPage() {
        var active = document.querySelector('.pagination .active, .pagination li.active');
        if (active) {
            var n = parseInt((active.textContent || '').replace(/\D+/g, ''), 10);
            if (n > 0) { return n; }
        }
        var m = window.location.href.match(/[?&\/]page[-=](\d+)/);
        return m ? parseInt(m[1], 10) : 1;
    }
    function faqApply() {
        var el = document.getElementById('accordion-faq');
        if (!el) { return; }
        var box = (el.closest && el.closest('.container')) || el.parentNode || el;
        box.style.display = faqPage() > 1 ? 'none' : '';
    }
    var lastPage = null;
    function tick() {
        var p = faqPage();
        if (p !== lastPage) {
            lastPage = p;
            faqApply();
        }
    }
    setInterval(tick, 300);
    window.addEventListener('popstate', tick);
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tick);
    } else {
        tick();
    }
})();
JS;

        return '<script>' . $js . '</script>';
    }

    private function buildFaqStyle()
    {
        return '<style>.category-faq-row{justify-content:flex-end;}</style>';
    }

    private function buildFaqSchema($faqs)
    {
        $entities = array();

        foreach ($faqs as $faq) {
            $question = $this->cleanText($faq['question']);
            $answer = $this->cleanText($faq['answer']);

            if ($question === '' || $answer === '') {
                continue;
            }

            $entities[] = array(
                '@type' => 'Question',
                'name' => $question,
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => $answer,
                ),
            );
        }

        if (!$entities) {
            return '';
        }

        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        );

        $json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            return '';
        }

        return '<script type="application/ld+json">' . $json . '</script>';
    }

    private function cleanText($value)
    {
        $text = strip_tags((string) $value);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }
}
