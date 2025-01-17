<?php
namespace Coercive\Utility\Template;

use Coercive\App\Service\Container;
use Exception;

/**
 * Template System
 *
 * @package Coercive\Utility\Template
 * @link https://github.com/Coercive/Template
 *
 * @author Anthony Moral <contact@coercive.fr>
 * @copyright 2025
 * @license MIT
 */
class Template extends Container
{
	const OPTION_ID = 'id';
	const OPTION_CLASS = 'class';
	const OPTION_NAMESPACE = 'namespace';
	const OPTION_TYPE = 'type';
	const OPTION_NAME = 'name';
	const OPTION_DESCRIPTION = 'description';
	const OPTION_POSITIONS = 'positions';
	const OPTION_WRAPPER = 'wrapper';
	const OPTION_CONTENT = 'content';
	const OPTION_DATA = 'data';
	const OPTIONS = [
		self::OPTION_ID,
		self::OPTION_CLASS,
		self::OPTION_NAMESPACE,
		self::OPTION_TYPE,
		self::OPTION_NAME,
		self::OPTION_DESCRIPTION,
		self::OPTION_POSITIONS,
		self::OPTION_WRAPPER,
		self::OPTION_CONTENT,
		self::OPTION_DATA,
	];

	const CONTENT_REPLACEMENT_TAG = '{{content}}';

	/** @var Template[] */
	private array $allMap = [];

	/** @var Template[] */
	private array $idMap = [];

	/** @var Template[] */
	private array $typeMap = [];

	/** @var Template[] */
	private array $classMap = [];

	/** @var Template[] */
	private array $namespaceMap = [];

	/** @var Template[] */
	private array $positions = [];

	private Container $data;

	/**
	 * Template constructor.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->data = new Container;
	}

	/**
	 * @return Template[]
	 */
	public function getAllMap(): array
	{
		return $this->allMap;
	}

	/**
	 * @return Template[]
	 */
	public function getIdMap(): array
	{
		return $this->idMap;
	}

	/**
	 * @return Template[]
	 */
	public function getTypeMap(): array
	{
		return $this->typeMap;
	}

	/**
	 * @return Template[]
	 */
	public function getClassMap(): array
	{
		return $this->classMap;
	}

	/**
	 * @return Template[]
	 */
	public function getNamespaceMap(): array
	{
		return $this->namespaceMap;
	}

	/**
	 * @return void
	 * @throws Exception
	 */
	private function addToMap()
	{
		if(!$spl = spl_object_id($this)) {
			throw new Exception('spl_object_id return empty value');
		}
		$this->allMap[$spl] = $this;

		if($id = $this->getInternalId()) {
			$this->idMap[$id] = $this;
		}
		if($type = $this->getInternalType()) {
			$this->typeMap[$type][$spl] = $this;
		}
		if($class = $this->getInternalClass()) {
			$this->classMap[$class][$spl] = $this;
		}
		if($namespace = $this->getInternalNamespace()) {
			$this->namespaceMap[$namespace][$spl] = $this;
		}
	}

	/**
	 * Map all positions inside this one
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function automap(): self
	{
		$this->allMap = $this->idMap = $this->typeMap = $this->classMap = $this->namespaceMap = [];
		$this->addToMap();
		foreach ($this->getPositions() as $position) {
			$position->automap();
			$this->allMap = array_merge($this->idMap, $position->getAllMap());
			$this->idMap = array_merge($this->idMap, $position->getIdMap());
			$this->typeMap = array_merge_recursive($this->typeMap, $position->getTypeMap());
			$this->classMap = array_merge_recursive($this->classMap, $position->getClassMap());
			$this->namespaceMap = array_merge_recursive($this->namespaceMap, $position->getNamespaceMap());
		}
		return $this;
	}

	/**
	 * @param string $id
	 * @param string $wrapper
	 * @return $this
	 */
	public function setWrapperById(string $id, string $wrapper): self
	{
		if($position = $this->getPosition($id)) {
			$position->setWrapper($wrapper);
		}
		return $this;
	}

	/**
	 * @param string $type
	 * @param string $wrapper
	 * @return $this
	 */
	public function setWrapperByType(string $type, string $wrapper): self
	{
		foreach ($this->typeMap[$type] ?? [] as $position) {
			$position->setWrapper($wrapper);
		}
		return $this;
	}

	/**
	 * @param string $class
	 * @param string $wrapper
	 * @return $this
	 */
	public function setWrapperByClass(string $class, string $wrapper): self
	{
		foreach ($this->classMap[$class] ?? [] as $position) {
			$position->setWrapper($wrapper);
		}
		return $this;
	}

	/**
	 * @param string $namespace
	 * @param string $wrapper
	 * @return $this
	 */
	public function setWrapperByNamespace(string $namespace, string $wrapper): self
	{
		foreach ($this->namespaceMap[$namespace] ?? [] as $position) {
			$position->setWrapper($wrapper);
		}
		return $this;
	}

	/**
	 * @param string $id
	 * @param string $content
	 * @return $this
	 */
	public function setContentById(string $id, string $content): self
	{
		if($position = $this->getPosition($id)) {
			$position->setContent($content);
		}
		return $this;
	}

	/**
	 * @param string $type
	 * @param string $content
	 * @return $this
	 */
	public function setContentByType(string $type, string $content): self
	{
		foreach ($this->typeMap[$type] ?? [] as $position) {
			$position->setContent($content);
		}
		return $this;
	}

	/**
	 * @param string $class
	 * @param string $content
	 * @return $this
	 */
	public function setContentByClass(string $class, string $content): self
	{
		foreach ($this->classMap[$class] ?? [] as $position) {
			$position->setContent($content);
		}
		return $this;
	}

	/**
	 * @param string $namespace
	 * @param string $content
	 * @return $this
	 */
	public function setContentByNamespace(string $namespace, string $content): self
	{
		foreach ($this->namespaceMap[$namespace] ?? [] as $position) {
			$position->setContent($content);
		}
		return $this;
	}

	/**
	 * @param string $class
	 * @return $this
	 */
	public function setInternalClass(string $class): self
	{
		return $this->set(self::OPTION_CLASS, $class);
	}

	/**
	 * @return string
	 */
	public function getInternalClass(): string
	{
		return (string) $this->get(self::OPTION_CLASS);
	}

	/**
	 * @param string $namespace
	 * @return $this
	 */
	public function setInternalNamespace(string $namespace): self
	{
		return $this->set(self::OPTION_NAMESPACE, $namespace);
	}

	/**
	 * @return string
	 */
	public function getInternalNamespace(): string
	{
		return (string) $this->get(self::OPTION_NAMESPACE);
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setInternalType(string $type): self
	{
		return $this->set(self::OPTION_TYPE, $type);
	}

	/**
	 * @return string
	 */
	public function getInternalType(): string
	{
		return (string) $this->get(self::OPTION_TYPE);
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setInternalName(string $name): self
	{
		return $this->set(self::OPTION_NAME, $name);
	}

	/**
	 * @return string
	 */
	public function getInternalName(): string
	{
		return (string) $this->get(self::OPTION_NAME);
	}

	/**
	 * @param string $id
	 * @return $this
	 */
	public function setInternalId(string $id): self
	{
		return $this->set(self::OPTION_ID, $id);
	}

	/**
	 * @return string
	 */
	public function getInternalId(): string
	{
		return (string) $this->get(self::OPTION_ID);
	}

	/**
	 * @param string $description
	 * @return $this
	 */
	public function setInternalDescription(string $description): self
	{
		return $this->set(self::OPTION_DESCRIPTION, $description);
	}

	/**
	 * @return string
	 */
	public function getInternalDescription(): string
	{
		return (string) $this->get(self::OPTION_DESCRIPTION);
	}

	/**
	 * @return Container
	 */
	public function data(): Container
	{
		return $this->data;
	}

	/**
	 * @param Template $position
	 * @return $this
	 */
	public function addPosition(Template $position): self
	{
		$this->positions[] = $position;
		return $this;
	}

	/**
	 * @param Template[] $positions
	 * @return $this
	 */
	public function addPositions(array $positions): self
	{
		foreach ($positions as $position) {
			$this->addPosition($position);
		}
		return $this;
	}

	/**
	 * @return Template[]
	 */
	public function getPositions(): array
	{
		return $this->positions;
	}

	/**
	 * @param string $type
	 * @return Template[]
	 */
	public function getPositionByType(string $type): array
	{
		$positions = [];
		foreach ($this->typeMap[$type] ?? [] as $position) {
			$positions[] = $position;
		}
		return $positions;
	}

	/**
	 * @param string $class
	 * @return Template[]
	 */
	public function getPositionByClass(string $class): array
	{
		$positions = [];
		foreach ($this->classMap[$class] ?? [] as $position) {
			$positions[] = $position;
		}
		return $positions;
	}

	/**
	 * @param string $namespace
	 * @return Template[]
	 */
	public function getPositionByNamespace(string $namespace): array
	{
		$positions = [];
		foreach ($this->namespaceMap[$namespace] ?? [] as $position) {
			$positions[] = $position;
		}
		return $positions;
	}

	/**
	 * @param string $id
	 * @return Template|null
	 */
	public function getPosition(string $id): ? Template
	{
		if(array_key_exists($id, $this->idMap)) {
			return $this->idMap[$id];
		}
		return null;
	}


	/**
	 * @param string $wrapper
	 * @return $this
	 */
	public function setWrapper(string $wrapper): self
	{
		return $this->set(self::OPTION_WRAPPER, $wrapper);
	}

	/**
	 * @return string
	 */
	public function getWrapper(): string
	{
		return (string) $this->get(self::OPTION_WRAPPER);
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapper(): self
	{
		return $this->setWrapper(self::CONTENT_REPLACEMENT_TAG);
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperIfEmpty(): self
	{
		if(!$this->getWrapper()) {
			$this->setDefaultContentWrapper();
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperForAll(): self
	{
		foreach ($this->allMap as $position) {
			$position->setDefaultContentWrapper();
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperIfEmptyForAll(): self
	{
		foreach ($this->allMap as $position) {
			$position->setDefaultContentWrapperIfEmpty();
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperForAllByTypes(array $types): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalType(), $types, true)) {
				$position->setDefaultContentWrapper();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperIfEmptyForAllByTypes(array $types): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalType(), $types, true)) {
				$position->setDefaultContentWrapperIfEmpty();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperForAllByClasses(array $classes): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalClass(), $classes, true)) {
				$position->setDefaultContentWrapper();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperIfEmptyForAllByClasses(array $classes): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalClass(), $classes, true)) {
				$position->setDefaultContentWrapperIfEmpty();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperForAllByIds(array $ids): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalId(), $ids, true)) {
				$position->setDefaultContentWrapper();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperIfEmptyForAllByIds(array $ids): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalId(), $ids, true)) {
				$position->setDefaultContentWrapperIfEmpty();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperForAllByNamespaces(array $namespaces): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalNamespace(), $namespaces, true)) {
				$position->setDefaultContentWrapper();
			}
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setDefaultContentWrapperIfEmptyForAllByNamespaces(array $namespaces): self
	{
		foreach ($this->allMap as $position) {
			if(in_array($position->getInternalNamespace(), $namespaces, true)) {
				$position->setDefaultContentWrapperIfEmpty();
			}
		}
		return $this;
	}

	/**
	 * @param string $content
	 * @return $this
	 */
	public function setContent(string $content): self
	{
		return $this->set(self::OPTION_CONTENT, $content);
	}

	/**
	 * @return string
	 */
	public function getContent(): string
	{
		return (string) $this->get(self::OPTION_CONTENT);
	}

	/**
	 * @return string
	 */
	public function getHtml(): string
	{
		$html = $this->getWrapper();
		if(false !== strpos($html, self::CONTENT_REPLACEMENT_TAG)) {
			if($this->exists(self::OPTION_CONTENT)) {
				$content = $this->getContent();
			}
			else {
				$content = '';
				foreach ($this->getPositions() as $position) {
					$content .= $position->getHtml();
				}
			}
			$html = str_replace(self::CONTENT_REPLACEMENT_TAG, $content, $html);
		}
		return $html;
	}
}