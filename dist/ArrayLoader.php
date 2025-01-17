<?php
namespace Coercive\Utility\Template;

use Exception;

/**
 * Class ArrayLoader
 *
 * @see Template
 */
class ArrayLoader
{
	/**
	 * Parse options and set them inside the Template object recursively
	 *
	 * @param array $positions
	 * @return array
	 * @throws Exception
	 */
	static private function loadPositions(array $positions): array
	{
		$templates = [];
		foreach ($positions as $position) {
			$template = new Template;
			foreach (Template::OPTIONS as $name) {
				if($value = $position[$name] ?? null) {
					switch ($name) {
						case Template::OPTION_ID:
						case Template::OPTION_CLASS:
						case Template::OPTION_NAMESPACE:
						case Template::OPTION_TYPE:
						case Template::OPTION_NAME:
						case Template::OPTION_DESCRIPTION:
						case Template::OPTION_WRAPPER:
						case Template::OPTION_CONTENT:
							$template->set($name, $value);
							break;
						case Template::OPTION_POSITIONS:
							$template->addPositions(self::loadPositions($value));
							break;
						case Template::OPTION_DATA:
							$template->data()->from($value);
							break;
						default:
							throw new Exception("Unknown option : $name");
					}
				}
			}
			$templates[] = $template;
		}
		return $templates;
	}

	/**
	 * Load MANY templates
	 *
	 * @param array $templates
	 * @return Template[]
	 * @throws Exception
	 */
	static public function load(array $templates): array
	{
		return self::loadPositions($templates);
	}
}