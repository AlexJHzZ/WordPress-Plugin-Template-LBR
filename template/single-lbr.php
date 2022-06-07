<?php get_header(); 
while (have_posts()) : the_post(); 
$post_id=get_the_ID();
$post = get_post($post_id); 
?>	
<main id="site-content" role="main">
	<?php
		echo get_the_title();
	?>
	<!-- CONTENT HERE -->
</main>
<?php endwhile; ?>
<?php get_footer(); ?>