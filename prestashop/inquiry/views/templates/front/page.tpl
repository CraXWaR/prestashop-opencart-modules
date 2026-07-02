{extends file='page.tpl'}

{block name='page_title'}
    {l s='Запитвания' mod='inquiry'}
{/block}

{block name='page_content'}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Spectral:ital,wght@0,400;0,500;0,600;1,400&display=swap">

    {if $recaptcha_site_key}
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    {/if}

    <div class="inquiry">

        {if isset($smarty.get.submitted) && $smarty.get.submitted}
            <div class="inq-alert inq-alert-ok" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                {l s='Благодарим! Вашето запитване е изпратено и ще се появи след одобрение.' mod='inquiry'}
            </div>
        {/if}

        {if isset($smarty.get.error) && $smarty.get.error}
            <div class="inq-alert inq-alert-err" role="alert">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                {l s='За съжаление запитването не можа да бъде изпратено. Моля, проверете данните си и опитайте отново.' mod='inquiry'}
            </div>
        {/if}

        <header class="inq-head">
            <p class="inq-lead">{l s='Изпратете запитване и вижте какво питат други.' mod='inquiry'}</p>
            <button type="button" id="inq-trigger" class="inq-trigger" aria-haspopup="dialog" aria-expanded="false" aria-controls="inq-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>{l s='Оставете запитване' mod='inquiry'}</span>
            </button>
        </header>

        <div id="inq-modal-overlay" class="inq-modal-overlay" aria-hidden="true">
            <div id="inq-modal" class="inq-modal" role="dialog" aria-modal="true" aria-labelledby="inq-modal-title">
                <button type="button" id="inq-modal-close" class="inq-modal-close" aria-label="{l s='Затвори' mod='inquiry'}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <p id="inq-modal-title" class="inq-form-title">{l s='Изпратете запитване' mod='inquiry'}</p>

                <div class="inq-hours">
                    <p class="inq-hours-title">{l s='Работно време' mod='inquiry'}</p>
                    <p class="inq-hours-text">{l s='ПОНЕДЕЛНИК - ПЕТЪК | 09:00 - 17:00' mod='inquiry'}</p>
                </div>

                <form method="post" action="{$submit_url}">
                    <input type="text" name="website" class="inq-honeypot" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="inq-field">
                        <label for="inquiry-email">{l s='Имейл' mod='inquiry'}</label>
                        <input type="email" id="inquiry-email" name="email" placeholder="you@example.com" required>
                    </div>
                    <div class="inq-field">
                        <label for="inquiry-title">{l s='Заглавие' mod='inquiry'}</label>
                        <input type="text" id="inquiry-title" name="title" placeholder="{l s='Дайте заглавие на запитването' mod='inquiry'}" maxlength="255" required>
                    </div>
                    <div class="inq-field">
                        <label for="inquiry-content">{l s='Запитване' mod='inquiry'}</label>
                        <textarea id="inquiry-content" name="content" rows="4" placeholder="{l s='Какво мислите?' mod='inquiry'}" required></textarea>
                    </div>
                    <div class="inq-field inq-field-checkbox">
                        <label class="inq-checkbox-label">
                            <input type="checkbox" name="terms_accepted" required>
                            <span>{l s='Съгласен съм с' mod='inquiry'} <a href="{$terms_url}" target="_blank" rel="noopener">{l s='Общите условия' mod='inquiry'}</a></span>
                        </label>
                    </div>
                    <div class="inq-field inq-field-checkbox">
                        <label class="inq-checkbox-label">
                            <input type="checkbox" name="privacy_accepted" required>
                            <span>{l s='Съгласен съм с' mod='inquiry'} <a href="{$privacy_url}" target="_blank" rel="noopener">{l s='Условието за поверителност' mod='inquiry'}</a></span>
                        </label>
                    </div>
                    {if $recaptcha_site_key}
                        <div class="inq-field">
                            <div class="g-recaptcha" data-sitekey="{$recaptcha_site_key|escape:'html':'UTF-8'}"></div>
                        </div>
                    {/if}
                    <button type="submit" name="submit_inquiry" class="inq-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        {l s='Изпрати' mod='inquiry'}
                    </button>
                </form>
            </div>
        </div>

        <hr class="inq-divider">

        {if $search_query !== '' || $inq_pagination.total_inquiries > 0}
            <form method="get" action="{$search_action}" class="inq-search" role="search">
                {foreach from=$search_hidden key=hk item=hv}
                    <input type="hidden" name="{$hk|escape:'html':'UTF-8'}" value="{$hv|escape:'html':'UTF-8'}">
                {/foreach}
                <div class="inq-search-field">
                    <svg class="inq-search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="search" name="q" class="inq-search-input" value="{$search_query|escape:'html':'UTF-8'}" placeholder="{l s='Търсене в запитванията…' mod='inquiry'}">
                    {if $search_query !== ''}
                        <a href="{$search_url}" class="inq-search-clear" aria-label="{l s='Изчисти търсенето' mod='inquiry'}" title="{l s='Изчисти търсенето' mod='inquiry'}">&times;</a>
                    {/if}
                </div>
                <button type="submit" class="inq-search-btn">{l s='Търси' mod='inquiry'}</button>
            </form>
        {/if}

        {if $categories|@count > 0}
            <div class="inq-cat-slider">
                <button type="button" id="inq-cat-prev" class="inq-cat-arrow inq-cat-arrow-hidden" aria-label="{l s='Предишни категории' mod='inquiry'}">‹</button>
                <div class="inq-cat-track-wrap">
                    <div id="inq-cat-track" class="inq-cat-track">
                        <a href="{$search_action}" class="inq-cat-pill{if $current_categories|@count == 0} inq-cat-pill-active{/if}">
                            {l s='Всички' mod='inquiry'}
                        </a>
                        {foreach $categories as $cat}
                            <a href="{$cat.url}" class="inq-cat-pill{if $cat.active} inq-cat-pill-active{/if}">
                                {$cat.name|escape:'html':'UTF-8'}
                            </a>
                        {/foreach}
                    </div>
                </div>
                <button type="button" id="inq-cat-next" class="inq-cat-arrow" aria-label="{l s='Следващи категории' mod='inquiry'}">›</button>
            </div>
        {/if}

        {if $inq_pagination.total_inquiries > 0}
            <p class="inq-section-label">
                {if $search_query !== ''}
                    {if $inq_pagination.total_inquiries == 1}
                        {l s='%d резултат' sprintf=[$inq_pagination.total_inquiries] mod='inquiry'}
                    {else}
                        {l s='%d резултата' sprintf=[$inq_pagination.total_inquiries] mod='inquiry'}
                    {/if}
                {else}
                    {if $inq_pagination.total_inquiries == 1}
                        {l s='%d запитване' sprintf=[$inq_pagination.total_inquiries] mod='inquiry'}
                    {else}
                        {l s='%d запитвания' sprintf=[$inq_pagination.total_inquiries] mod='inquiry'}
                    {/if}
                {/if}
            </p>
        {/if}

        {foreach from=$inquiries item=inquiry}
            <article class="inq-inquiry">
                <div class="inq-inquiry-body-wrap">
                    <h3 class="inq-inquiry-title">{$inquiry.title|escape:'html':'UTF-8'}</h3>
                    <p class="inq-inquiry-meta">{$inquiry.date_add}</p>
                    <p class="inq-inquiry-body">{$inquiry.content|escape:'html':'UTF-8'|nl2br nofilter}</p>

                    <a href="{$inquiry.url}" class="inq-read-more">{l s='Прочети повече' mod='inquiry'}</a>

                    {if $inquiry.admin_reply}
                        <div class="inq-reply">
                            <span class="inq-reply-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 0 0-4-4H4"/></svg>
                                {l s='Отговор от магазина' mod='inquiry'}
                            </span>
                            {if $inquiry.employee_name}<span class="inq-reply-author">{$inquiry.employee_name|escape:'html':'UTF-8'}</span>{/if}
                            <p class="inq-reply-text">{$inquiry.admin_reply|escape:'html':'UTF-8'|nl2br nofilter}</p>
                        </div>
                    {/if}

                    {if $inquiry.products|@count}
                        <div class="inq-products">
                            <p class="inq-products-label">{l s='Свързани продукти' mod='inquiry'}</p>
                            <div class="inq-products-list">
                                {foreach $inquiry.products as $product}
                                    <a href="{$product.url}" class="inq-product-card" target="_blank">
                                        {if $product.image_url}
                                            <img src="{$product.image_url}" alt="{$product.name|escape:'html':'UTF-8'}" class="inq-product-img">
                                        {/if}
                                        <span class="inq-product-name">{$product.name|escape:'html':'UTF-8'}</span>
                                    </a>
                                {/foreach}
                            </div>
                        </div>
                    {/if}

                </div>
            </article>
        {foreachelse}
            <div class="inq-empty">
                {if $search_query !== ''}
                    <p>{l s='Няма запитвания, отговарящи на търсенето.' mod='inquiry'}</p>
                {else}
                    <p>{l s='Все още няма запитвания. Бъдете първият!' mod='inquiry'}</p>
                {/if}
            </div>
        {/foreach}

        {if $inq_pagination.total_pages > 1}
            <nav class="inq-pagination" aria-label="{l s='Страници със запитвания' mod='inquiry'}">
                {if $inq_pagination.prev_url}
                    <a class="inq-page inq-page-nav" href="{$inq_pagination.prev_url}" rel="prev" aria-label="{l s='Предишна страница' mod='inquiry'}">‹</a>
                {else}
                    <span class="inq-page inq-page-nav inq-page-disabled" aria-hidden="true">‹</span>
                {/if}

                {foreach from=$inq_pagination.pages item=pg}
                    {if $pg.is_gap}
                        <span class="inq-page-gap">…</span>
                    {elseif $pg.current}
                        <span class="inq-page inq-page-current" aria-current="page">{$pg.number}</span>
                    {else}
                        <a class="inq-page" href="{$pg.url}">{$pg.number}</a>
                    {/if}
                {/foreach}

                {if $inq_pagination.next_url}
                    <a class="inq-page inq-page-nav" href="{$inq_pagination.next_url}" rel="next" aria-label="{l s='Следваща страница' mod='inquiry'}">›</a>
                {else}
                    <span class="inq-page inq-page-nav inq-page-disabled" aria-hidden="true">›</span>
                {/if}
            </nav>
        {/if}
    </div>
{/block}
