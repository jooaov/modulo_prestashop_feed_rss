<section id="dashactivity" class="panel widget{if $allow_push} allow_push{/if}">
    <div class="panel-heading">
		<i class="icon-rss"></i> Feed RSS
	</div>
    <div class="dash_news_content">
        {foreach $list as $row}
            <article>
                <h4>
                    <a href={$row['link']} class="_blank" onclick="return !window.open(this.href);">
                    {$row['title']}</a>
                </h4>
                <span class="dash-news-date text-muted">{$row['pubDate']}</span>
                <p>
                    {$row['description']}
                </p>
                <p class="text-muted">
                        {$row['author']}
                </p>
            </article>
            <hr>
        {/foreach}
    </div>
</section>