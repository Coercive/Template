<?php
namespace Coercive\Utility\Template;

use Exception;
use Symfony\Component\Yaml\Parser;

/**
 * Class YamlLoader
 *
 * @see Template
 */
class YamlLoader
{
	/**
	 * @param array $filesnames
	 * @return Template[]
	 * @throws Exception
	 */
	static public function load(array $filesnames): array
	{
		$templates = [];
		$parser = new Parser;
		foreach ($filesnames as $filesname) {
			if(!is_file($filesname)) {
				throw new Exception("Not a valid file $filesname");
			}
			if(!$raw = file_get_contents($filesname)) {
				throw new Exception("No content for $filesname");
			}
			$data = $parser->parse($raw);
			if(!$data || !is_array($data)) {
				throw new Exception("Not a valid content for $filesname");
			}
			$templates = array_merge($templates, ArrayLoader::load($data));
		}
		return $templates;
	}
}