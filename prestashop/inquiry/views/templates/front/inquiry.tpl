{extends file='page.tpl'}

{block name='page_title'}
    {$inquiry.title|escape:'html':'UTF-8'}
{/block}

{block name='page_content'}
    {* Same faces as the inquiry list (both carry Cyrillic) *}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Spectral:ital,wght@0,400;0,500;0,600;1,400&display=swap">

    <div class="inquiry inquiry-single">

        <a href="{$back_url}" class="inq-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            {l s='Към всички запитвания' mod='inquiry'}
        </a>

        <article class="inq-inquiry inq-inquiry-single">
            <h1 class="inq-inquiry-title">{$inquiry.title|escape:'html':'UTF-8'}</h1>
            <p class="inq-inquiry-meta">{$inquiry.date_add}</p>
            <div class="inq-inquiry-body">{$inquiry.content|escape:'html':'UTF-8'|nl2br nofilter}</div>

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
        </article>
    </div>
{/block}
