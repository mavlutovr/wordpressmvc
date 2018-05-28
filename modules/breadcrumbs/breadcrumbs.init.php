<?php
namespace Wdpro\Breadcrumbs;

\Wdpro\Autoload::add('Wdpro\Breadcrumbs', __DIR__);

wdpro_default_file(__DIR__.'/breadcrumbs_template.php',
	WDPRO_TEMPLATE_PATH.'breadcrumbs_template.php');


if (is_admin())
{
	$consoleBreadcrumbs = null;
	add_action('wdpro_console_breadcrumbs', function($return) use (&$consoleBreadcrumbs) {

		// post-new.php?post_type=app_menu_countries
		// post-new.php?post_type=app_menu_countries&sectionId=68
		if (!$consoleBreadcrumbs)
		{
			$consoleBreadcrumbs = new ConsoleBreadcrumbs();
		}

		if (isset($_GET['sectionId']) && $_GET['sectionId'])
		{
			$breadcrumbs = $consoleBreadcrumbs;
			$type = $_GET['post_type'];
			$breadcrumbs->makeFrom(
				wdpro_object_by_post_id($_GET['sectionId']),
				$type
			);
			$breadcrumbs->display();

			if ($return)
			{
				$return($breadcrumbs);
			}
		}

		else if (isset($_GET['post']) && $_GET['post'] && $_GET['action'] == 'edit')
		{
			$breadcrumbs = $consoleBreadcrumbs;
			$breadcrumbs->makeFrom(
				wdpro_object_by_post_id($_GET['post'])
			);
			$breadcrumbs->display();

			if ($return)
			{
				$return($breadcrumbs);
			}
		}
		
		else {
			if ($return)
			{
				/* @var \Wdpro\BasePage $entity */
				if (isset($_GET['post_type']) 
					&& $entity = wdpro_get_class_by_post_type($_GET['post_type']))
				{
					$breadcrumbs = $consoleBreadcrumbs;
					$breadcrumbs->setFirstRoll($entity::getConsoleRoll());
					$breadcrumbs->display();
					$return($breadcrumbs);
				}
			}
		}
	});
}
