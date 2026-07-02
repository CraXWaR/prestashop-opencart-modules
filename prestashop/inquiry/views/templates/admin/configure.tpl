<div class="inquiry-admin">
    <div class="inq-panel">
        <div class="inq-panel-heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> {l s='Запитвания' mod='inquiry'}
        </div>
        <div class="inq-panel-body">
            <p style="margin:0;"><strong>{l s='Публична страница:' mod='inquiry'}</strong> <a href="{$page_url}" target="_blank" rel="noopener">{$page_url}</a></p>
        </div>
    </div>

    <div class="inq-panel">
        <div class="inq-panel-heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg> {l s='Настройки' mod='inquiry'}
        </div>
        <form method="post" action="{$action_url}">
            <div class="inq-panel-body">
                <div class="inq-form-row">
                    <div class="inq-form-label">{l s='Автоматично одобрение' mod='inquiry'}</div>
                    <div class="inq-form-control">
                        <label class="inq-slider-wrap">
                            <input type="checkbox" name="INQUIRY_AUTO_APPROVE" id="auto_approve" value="1" {if $auto_approve}checked="checked"{/if}>
                            <span class="inq-slider"><span class="inq-slider-knob"></span></span>
                            <span class="inq-slider-status">
                                <span class="inq-status-on">{l s='Да' mod='inquiry'}</span>
                                <span class="inq-status-off">{l s='Не' mod='inquiry'}</span>
                            </span>
                        </label>
                        <p class="inq-help">{l s='Когато е включено, новите запитвания се публикуват веднага. Когато е изключено, остават в изчакване, докато ги одобрите.' mod='inquiry'}</p>
                    </div>
                </div>

                <div class="inq-form-row">
                    <div class="inq-form-label">{l s='SEO заглавие' mod='inquiry'}</div>
                    <div class="inq-form-control">
                        <input type="text" name="seo_meta_title" class="inq-input inq-input-full" value="{$seo_meta_title|escape:'html':'UTF-8'}">
                        <p class="inq-help">{l s='Налични променливи: %title%, %shop_name%. Ще бъде съкратено до 70 символа на страницата на запитването.' mod='inquiry'}</p>
                    </div>
                </div>

                <div class="inq-form-row">
                    <div class="inq-form-label">{l s='SEO описание' mod='inquiry'}</div>
                    <div class="inq-form-control">
                        <input type="text" name="seo_meta_desc" class="inq-input inq-input-full" value="{$seo_meta_desc|escape:'html':'UTF-8'}">
                        <p class="inq-help">{l s='Налични променливи: %title%, %shop_name%. Ще бъде съкратено до 160 символа на страницата на запитването.' mod='inquiry'}</p>
                    </div>
                </div>

                <div class="inq-form-row">
                    <div class="inq-form-label">{l s='reCAPTCHA Site Key' mod='inquiry'}</div>
                    <div class="inq-form-control">
                        <input type="text" name="recaptcha_site_key" class="inq-input inq-input-full" value="{$recaptcha_site_key|escape:'html':'UTF-8'}">
                        <p class="inq-help">{l s='Получава се от' mod='inquiry'} <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">google.com/recaptcha/admin</a> {l s='(reCAPTCHA v2 "Не съм робот").' mod='inquiry'}</p>
                    </div>
                </div>

                <div class="inq-form-row">
                    <div class="inq-form-label">{l s='reCAPTCHA Secret Key' mod='inquiry'}</div>
                    <div class="inq-form-control">
                        <input type="password" name="recaptcha_secret_key" class="inq-input inq-input-full" value="{$recaptcha_secret_key|escape:'html':'UTF-8'}" autocomplete="off">
                        <p class="inq-help">{l s='Ако тези две полета са празни, reCAPTCHA не се показва във формата за запитване.' mod='inquiry'}</p>
                    </div>
                </div>
            </div>
            <div class="inq-panel-footer">
                <button type="submit" name="submit_settings" class="inq-btn inq-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> {l s='Запази' mod='inquiry'}
                </button>
            </div>
        </form>
    </div>

    <div class="inq-panel">
        <div class="inq-panel-heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> {l s='Запитвания' mod='inquiry'}
            <span class="inq-badge">{$inquiries|@count}</span>
        </div>
        <div class="inq-panel-body">
            {if $inquiries|@count}
                {foreach $inquiries as $inquiry}
                    <div class="inq-card{if !$inquiry.approved} inq-card-pending{/if}">

                        <div class="inq-card-head">
                            <span class="inq-id">#{$inquiry.id_inquiry|intval}</span>
                            <span class="inq-card-meta">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                {$inquiry.date_add}
                                <span class="inq-dot">·</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                {$inquiry.email|escape:'html':'UTF-8'}
                            </span>
                            <span class="inq-card-status">
                                {if $inquiry.approved}
                                    <span class="inq-label inq-label-success">{l s='Одобрен' mod='inquiry'}</span>
                                {else}
                                    <span class="inq-label inq-label-warning">{l s='Изчакващ' mod='inquiry'}</span>
                                {/if}
                            </span>
                        </div>

                        <h3 class="inq-card-title">{$inquiry.title|escape:'html':'UTF-8'}</h3>
                        <p class="inq-card-body">{$inquiry.content|escape:'html':'UTF-8'|nl2br nofilter}</p>
                        <button type="button" class="inq-read-more">{l s='Прочети повече' mod='inquiry'}</button>

                        <div class="inq-edit">
                        <form id="inq-save-{$inquiry.id_inquiry|intval}" class="inq-save-form" method="post" action="{$action_url}">
                            <input type="hidden" name="id_inquiry" value="{$inquiry.id_inquiry|intval}">
                            <div class="inq-reply-head">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                                <span>{l s='Отговор от магазина' mod='inquiry'}</span>
                                {if $inquiry.id_employee && isset($employee_map[$inquiry.id_employee|intval])}
                                    <span class="inq-reply-by">— {$employee_map[$inquiry.id_employee|intval]|escape:'html':'UTF-8'}</span>
                                {/if}
                            </div>
                            <textarea name="admin_reply" rows="3" class="inq-textarea" placeholder="{l s='Напишете отговор…' mod='inquiry'}">{$inquiry.admin_reply|escape:'html':'UTF-8'}</textarea>
                        </form>

                        <div class="inq-bar">
                            <div class="inq-bar-selects">
                                <select name="id_category[]" form="inq-save-{$inquiry.id_inquiry|intval}" class="inq-select" multiple="multiple" data-ajax-url="{$ajax_search_categories_url}" data-placeholder="{l s='Започнете да пишете, за да изберете категория…' mod='inquiry'}" aria-label="{l s='Категория' mod='inquiry'}">
                                    {foreach $inquiry.categories as $cat}
                                        <option value="{$cat.id_category|intval}" selected="selected">
                                            {$cat.name|escape:'html':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>
                                <select name="id_product[]" form="inq-save-{$inquiry.id_inquiry|intval}" class="inq-product-select" multiple="multiple" data-ajax-url="{$ajax_search_products_url}" data-placeholder="{l s='Започнете да пишете, за да изберете продукт…' mod='inquiry'}" aria-label="{l s='Продукт' mod='inquiry'}">
                                    {foreach $inquiry.products as $product}
                                        <option value="{$product.id_product|intval}" selected="selected">
                                            {$product.name|escape:'html':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>

                        <div class="inq-bar-buttons">
                            <button type="submit" name="save_inquiry" form="inq-save-{$inquiry.id_inquiry|intval}" class="inq-btn inq-btn-primary inq-btn-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> {l s='Запази промените' mod='inquiry'}
                            </button>
                            {if !$inquiry.approved}
                                <form method="post" action="{$action_url}">
                                    <input type="hidden" name="id_inquiry" value="{$inquiry.id_inquiry|intval}">
                                    <button type="submit" name="approve_inquiry" class="inq-btn inq-btn-success inq-btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg> {l s='Одобри' mod='inquiry'}
                                    </button>
                                </form>
                            {/if}
                            <form method="post" action="{$action_url}" onsubmit="return confirm('{l s='Изтриване на това запитване?' js=1 mod='inquiry'}');">
                                <input type="hidden" name="id_inquiry" value="{$inquiry.id_inquiry|intval}">
                                <button type="submit" name="delete_inquiry" class="inq-btn inq-btn-danger inq-btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg> {l s='Изтрий' mod='inquiry'}
                                </button>
                            </form>
                        </div>
                        </div>

                    </div>
                {/foreach}
            {else}
                <p class="inq-empty">{l s='Няма намерени запитвания.' mod='inquiry'}</p>
            {/if}
        </div>
    </div>

</div>

<script>
var INQ_MORE = "{l s='Прочети повече' mod='inquiry' js=1}";
var INQ_LESS = "{l s='Скрий' mod='inquiry' js=1}";
{literal}
(function () {
    var root = document.querySelector('.inquiry-admin');

    // Read-more toggle on the inquiry body (clamped to 3 lines)
    if (root && !root.dataset.inqInit) {
        root.dataset.inqInit = '1';

        Array.prototype.forEach.call(root.querySelectorAll('.inq-card-body'), function (body) {
            var btn = body.parentNode.querySelector('.inq-read-more');
            if (btn && body.scrollHeight - body.clientHeight > 2) {
                btn.style.display = 'inline-flex';
            }
        });

        root.addEventListener('click', function (e) {
            var btn = e.target.closest('.inq-read-more');
            if (!btn) { return; }
            e.preventDefault();
            var body = btn.parentNode.querySelector('.inq-card-body');
            if (!body) { return; }
            var expanded = body.classList.toggle('is-expanded');
            btn.textContent = expanded ? INQ_LESS : INQ_MORE;
        });
    }

    window.addEventListener('load', function () {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.select2) { return; }
        jQuery('.inquiry-admin .inq-product-select, .inquiry-admin .inq-select').each(function () {
            var $el = jQuery(this);
            if ($el.hasClass('select2-hidden-accessible')) { return; }
            $el.select2({
                ajax: {
                    url: $el.data('ajax-url'),
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term };
                    }
                },
                placeholder: $el.data('placeholder'),
                allowClear: true,
                width: '100%',
                dropdownCssClass: 'inq-select2-drop'
            });
        });
    });
}());
{/literal}
</script>
