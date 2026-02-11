<?php
/**
 * HTML class file
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 *
 * @package Mantle
 */

namespace Mantle\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use InvalidArgumentException;
use Mantle\Contracts\Support\Htmlable;
use Mantle\Support\Traits\Conditionable;
use Mantle\Support\Traits\Macroable;
use Mantle\Support\Traits\Tappable;
use Symfony\Component\DomCrawler\Crawler as SymfonyCrawler;
use Mantle\Support\Internal\HTML_Helpers as Helpers;
use Mantle\Testing\Concerns\Element_Assertions;
use Override;

use function Mantle\Support\Helpers\classname;
use function Mantle\Support\Helpers\stringable;

/**
 * HTML class for parsing and manipulating HTML documents.
 *
 * This class extends the Symfony DomHTML and provides additional
 * methods for querying and modifying HTML elements using CSS selectors
 * and XPath expressions. It also includes methods for setting and
 * removing attributes, adding and removing classes, and modifying
 * elements using callback functions.
 *
 * Inspired by {@see https://github.com/wasinger/htmlpagedom}.
 *
 * @link https://symfony.com/doc/current/components/dom_crawler.html
 */
class HTML extends SymfonyCrawler implements Htmlable {
	use Conditionable;
	use Element_Assertions;
	use HTML\Assertions;
	use Macroable;
	use Tappable;

	/**
	 * The internal root element name used when importing html fragments.
	 * */
	private const FRAGMENT_ROOT_TAGNAME = '_root';

	/**
	 * Get an HTML object from a variety of types.
	 *
	 * @param string|\Symfony\Component\DomCrawler\Crawler|DOMNode|DOMNodeList<DOMNode> $content
	 */
	public static function create( string|\Symfony\Component\DomCrawler\Crawler|DOMNode|DOMNodeList $content ): static {
		return match ( true ) {
			$content instanceof static => $content,
			$content instanceof SymfonyCrawler => new static( iterator_to_array( $content ) ),
			default => new static( $content ),
		};
	}

	/**
	 * Convert the HTML instance to an HTML string.
	 */
	public function to_html(): string {
		if ( $this->is_html_document() ) {
			return (string) $this->get_dom_document()?->saveHTML();
		}

		$doc  = new \DOMDocument( '1.0', 'UTF-8' );
		$root = $doc->appendChild( $doc->createElement( self::FRAGMENT_ROOT_TAGNAME ) );

		foreach ( $this as $node ) {
			$root->appendChild( $doc->importNode( $node, true ) );
		}

		$html = trim( (string) $doc->saveHTML() );

		return (string) preg_replace( '@^<' . self::FRAGMENT_ROOT_TAGNAME . '[^>]*>|</' . self::FRAGMENT_ROOT_TAGNAME . '>$@', '', $html );
	}

	/**
	 * Adds a node to the current list of nodes.
	 *
	 * This method uses the appropriate specialized add*() method based
	 * on the type of the argument.
	 *
	 * @param \DOMNodeList<DOMNode>|\DOMNode|array<mixed>|string|HTML|null $node A node.
	 */
	#[Override]
	public function add( \DOMNodeList|\DOMNode|array|string|SymfonyCrawler|null $node ): void {
		if ( $node instanceof SymfonyCrawler ) {
			foreach ( $node as $childnode ) {
				$this->addNode( $childnode );
			}
		} else {
			parent::add( $node );
		}
	}

	/**
	 * Query the document for all elements matching a CSS selector.
	 *
	 * @param string $selector
	 */
	public function get_by_selector( string $selector ): static {
		return $this->filter( $selector );
	}

	/**
	 * Query the document for a single element matching a CSS selector.
	 *
	 * @param string $id The id to match (without the #).
	 */
	public function first_by_id( string $id ): static {
		if ( '#' === $id[0] ) {
			$id = substr( $id, 1 );
		}

		return $this->filter( "#{$id}" )->first();
	}

	/**
	 * Query the document for a single element using a tag name.
	 *
	 * @param string $tag The tag name to match.
	 */
	public function first_by_tag( string $tag ): static {
		return $this->filter( $tag )->first();
	}

	/**
	 * Query the document for a single element using a class name.
	 *
	 * @param string $test_id The test ID.
	 */
	public function first_by_testid( string $test_id ): static {
		return $this->filter( "[data-testid=\"{$test_id}\"]" )->first();
	}

	/**
	 * Query the document for a single element using a CSS selector.
	 *
	 * @param string $selector The CSS selector to match.
	 */
	public function first_by_selector( string $selector ): static {
		return $this->filter( $selector )->first();
	}

	/**
	 * Get the nearest previous sibling element.
	 *
	 * @param string|null $selector Optional CSS selector to filter the previous siblings.
	 * @throws \InvalidArgumentException If the current node list is empty.
	 */
	public function previous_sibling( ?string $selector = null ): static {
		if ( ! $this->has_nodes() ) {
			throw new \InvalidArgumentException( 'The current node list is empty.' );
		}

		$previous = $this->previousAll();
		return (bool) $selector ? $previous->filter( $selector )->first() : $previous->first();
	}

	/**
	 * Get the nearest next sibling element.
	 *
	 * @param string|null $selector Optional CSS selector to filter the next siblings.
	 * @throws \InvalidArgumentException If the current node list is empty.
	 */
	public function next_sibling( ?string $selector = null ): static {
		if ( ! $this->has_nodes() ) {
			throw new \InvalidArgumentException( 'The current node list is empty.' );
		}

		$next = $this->nextAll();
		return (bool) $selector ? $next->filter( $selector )->first() : $next->first();
	}

	/**
	 * Retrieve all elements matching an XPath expression.
	 *
	 * @param string $xpath XPath expression to match.
	 */
	public function get_by_xpath( string $xpath ): static {
		return $this->filterXPath( $xpath );
	}

	/**
	 * Retrieve all elements matching a specific tag name.
	 *
	 * @param string $tag The tag name to match.
	 */
	public function get_by_tag( string $tag ): static {
		return $this->filter( $tag );
	}

	/**
	 * Retrieve all elements matching a specific test ID (data-testid) attribute.
	 *
	 * @param string $test_id The test ID.
	 */
	public function get_by_testid( string $test_id ): static {
		return $this->filter( "[data-testid=\"{$test_id}\"]" );
	}

	/**
	 * Query the document for a single element using an XPath expression.
	 *
	 * @param string $xpath
	 */
	public function first_by_xpath( string $xpath ): static {
		return $this->filterXPath( $xpath )->first();
	}

	/**
	 * Get the tag name of the first element in the HTML instance.
	 */
	public function tag_name(): string {
		return $this->nodeName();
	}

	/**
	 * Modify the elements using a callback function.
	 *
	 * @param callable $callback A callback function that receives the matched element and its index.
	 * @phpstan-param callable(HTML $crawler, int $i): (DOMNode|string|HTML|null) $callback
	 */
	public function modify( callable $callback ): static {
		$this->each( function ( HTML $item, int $index ) use ( $callback ): HTML {
			$result = $callback( $item, $index );

			// If the callback returns null, we can assume the callback modified the
			// node and we can return it as-is.
			if ( null === $result || $result instanceof static ) {
				return $item;
			}

			// Convert the result to a DOMNode if it's a string.
			if ( is_string( $result ) ) {
				$result = ( new static( $result ) )->getNode( 0 );
			}

			// If the callback did return something and it's a DOMNode we'll assume
			// they want to replace the node with the new one.
			if ( $result instanceof DOMNode ) {
				// If the new node is the same as the existing one, return the item.
				if ( $result === $item->getNode( 0 ) ) {
					return $item;
				}

				$node = $item->getNode( 0 );

				if ( $node instanceof \DOMNode ) {
					$node->parentNode?->replaceChild( static::import_new_node( $result, $node ), $node );
				}

				return $item;
			}

			throw new InvalidArgumentException( 'Callback must return null or a DOMNode instance.' );
		} );

		return $this;
	}

	/**
	 * Set an attribute for all elements in the HTML instance.
	 *
	 * @param string $name  The name of the attribute to set.
	 * @param string $value The value to set for the attribute.
	 */
	public function set_attribute( string $name, string $value ): static {
		foreach ( $this as $node ) {
			if ( $node instanceof DOMElement ) {
				$node->setAttribute( $name, $value );
			}
		}

		return $this;
	}

	/**
	 * Get the value of an attribute for the first element in the HTML instance.
	 *
	 * @param string      $name    The name of the attribute to retrieve.
	 * @param string|null $default The default value to return if the attribute is not found.
	 */
	public function get_attribute( string $name, ?string $default = null ): ?string {
		return $this->attr( $name, $default );
	}

	/**
	 * Set a data attribute for all elements in the HTML instance.
	 *
	 * @param string $name  The name of the data attribute to set (without "data-" prefix).
	 * @param string $value The value to set for the data attribute.
	 */
	public function set_data( string $name, string $value ): static {
		return $this->set_attribute( "data-{$name}", $value );
	}

	/**
	 * Remove an attribute from all elements in the HTML instance.
	 *
	 * @param string $name
	 */
	public function remove_attribute( string $name ): static {
		foreach ( $this as $node ) {
			if ( $node instanceof DOMElement ) {
				$node->removeAttribute( $name );
			}
		}

		return $this;
	}

	/**
	 * Remove a data attribute from all elements in the HTML instance.
	 *
	 * @param string $name The name of the data attribute to remove (without "data-" prefix).
	 */
	public function remove_data( string $name ): static {
		return $this->remove_attribute( "data-{$name}" );
	}

	/**
	 * Get the value of a data attribute for the first element in the HTML instance.
	 *
	 * @param string $name The name of the data attribute to retrieve (without "data-" prefix).
	 * @return string|null The value of the data attribute, or null if not found.
	 */
	public function get_data( string $name ): ?string {
		return $this->attr( "data-{$name}" );
	}

	/**
	 * Add a class to all elements in the HTML instance.
	 *
	 * @param string ...$class
	 */
	public function add_class( string ...$class ): static {
		$class = Arr::wrap( $class );

		foreach ( $this as $node ) {
			if ( $node instanceof DOMElement ) {
				$node->setAttribute( 'class', classname( $node->getAttribute( 'class' ), ...$class ) );
			}
		}

		return $this;
	}

	/**
	 * Remove a class from all elements in the HTML instance.
	 *
	 * @param string ...$class Class names to remove from the elements.
	 */
	public function remove_class( string ...$class ): static {
		$class = Arr::wrap( $class );

		foreach ( $this as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$existing = stringable( $node->getAttribute( 'class' ) )->explode( ' ' );

			// Remove the classes from the existing class list.
			$value = $existing->diff( $class )->implode( ' ' );

			if ( empty( $value ) ) {
				$node->removeAttribute( 'class' );
			} else {
				$node->setAttribute( 'class', $value );
			}
		}

		return $this;
	}

	/**
	 * Check if any of the elements in the HTML instance have a specific class.
	 *
	 * @param string ...$class Class names to check for.
	 */
	public function has_class( string ...$class ): bool {
		$class = Arr::wrap( $class );

		foreach ( $this as $node ) {
			if ( $node instanceof DOMElement ) {
				$existing = stringable( $node->getAttribute( 'class' ) )->explode( ' ' );

				if ( $existing->intersect( $class )->count() === count( $class ) ) {  // @phpstan-ignore-line argument.type
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if any of the elements in the HTML instance have any of the
	 * specified classes.
	 *
	 * @param string ...$class Class names to check for.
	 */
	public function has_any_class( string ...$class ): bool {
		$class = Arr::wrap( $class );

		foreach ( $this as $node ) {
			if ( $node instanceof DOMElement ) {
				$existing = stringable( $node->getAttribute( 'class' ) )->explode( ' ' );

				if ( $existing->intersect( $class )->is_not_empty() ) { // @phpstan-ignore-line argument.type
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Remove all elements matching a given CSS selector from the document.
	 *
	 * @param string $selector CSS selector to match.
	 */
	public function remove( string $selector ): static {
		$this->filter( $selector )->each( function ( HTML $item ): void {
			$node = $item->getNode( 0 );

			if ( $node instanceof \DOMNode && $node->parentNode instanceof \DOMNode ) {
				$node->parentNode->removeChild( $node );
			}
		} );

		return $this;
	}

	/**
	 * Prepend a new element to all elements in the HTML instance.
	 *
	 * @throws InvalidArgumentException If the provided element is invalid.
	 *
	 * @param string|HTML|DOMNode $element
	 */
	public function prepend( string|HTML|DOMNode $element ): static {
		$content = self::create( $element );
		$nodes   = [];

		foreach ( $this as $node ) {
			$ref_node = $node->firstChild;

			foreach ( $content as $new_node ) {
				$new_node = static::import_new_node( $new_node, $node );

				if ( ! $ref_node instanceof \DOMNode ) {
					$node->appendChild( $new_node );
				} elseif ( $ref_node !== $new_node ) {
					$node->insertBefore( $new_node, $ref_node );
				}

				$nodes[] = $new_node;
			}
		}

		$content->clear();
		$content->add( $nodes );

		return $this;
	}

	/**
	 * Append a new element to all elements in the HTML instance.
	 *
	 * @throws InvalidArgumentException If the provided element is invalid.
	 *
	 * @param string|HTML|DOMNode $element
	 */
	public function append( string|HTML|DOMNode $element ): static {
		$element = self::create( $element );
		$nodes   = [];

		foreach ( $this as $node ) {
			foreach ( $element as $new_node ) {
				$new_node = static::import_new_node( $new_node, $node );

				$node->appendChild( $new_node );

				$nodes[] = $new_node;
			}
		}

		$element->clear();
		$element->add( $nodes );

		return $this;
	}

	/**
	 * Insert content, specified by the parameter, after each element in the set of matched elements.
	 *
	 * @param string|self|DOMNode|DOMNodeList<DOMNode> $content
	 */
	public function after( string|self|DOMNode|DOMNodeList $content ): static {
		$content = self::create( $content );
		$nodes   = [];

		foreach ( $this as $node ) {
			$ref_node = $node->nextSibling;

			foreach ( $content as $new_node ) {
				$new_node = static::import_new_node( $new_node, $node );

				if ( ! $ref_node instanceof \DOMNode ) {
					$node->parentNode?->appendChild( $new_node );
				} else {
					$node->parentNode?->insertBefore( $new_node, $ref_node );
				}

				$nodes[] = $new_node;
			}
		}

		$content->clear();
		$content->add( $nodes );

		return $this;
	}

	/**
	 * Insert content, specified by the parameter, before each element in the set of matched elements.
	 *
	 * @param string|self|DOMNode|DOMNodeList<DOMNode> $content
	 */
	public function before( string|self|DOMNode|DOMNodeList $content ): static {
		$content = self::create( $content );
		$nodes   = [];

		foreach ( $this as $node ) {
			foreach ( $content as $newnode ) {
				if ( $node !== $newnode ) {
					$newnode = static::import_new_node( $newnode, $node );

					$node->parentNode?->insertBefore( $newnode, $node );

					$nodes[] = $newnode;
				}
			}
		}

		$content->clear();
		$content->add( $nodes );

		return $this;
	}

	/**
	 * Retrieve the next elements but not including the current in the HTML
	 * instance after the callback matches.
	 *
	 * @param callable $callback Callback to determine when to stop skipping elements.
	 * @phpstan-param callable(HTML $crawler): bool $callback
	 * @param bool     $include Whether to include the current/first element in the result.
	 */
	public function next_until( callable $callback, bool $include = false ): static {
		$matched = false;
		$crawler = new static( null );

		foreach ( $this as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			if ( $matched ) {
				$crawler->add( $node );
			} elseif ( $callback( new static( $node ) ) ) {
				$matched = true;

				if ( $include ) {
					$crawler->add( $node );
				}
			}
		}

		return $crawler;
	}

	/**
	 * Retrieve all elements until the callback matches, but not including the matched element.
	 *
	 * @param callable $callback Callback to determine when to stop adding elements.
	 * @phpstan-param callable(HTML $crawler): bool $callback
	 * @param bool     $include Whether to include the current/last element in the result.
	 */
	public function previous_until( callable $callback, bool $include = false ): static {
		$crawler = new static( null );

		foreach ( $this as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$matches = $callback( new static( $node ) );

			if ( ! $matches || $include ) {
				$crawler->add( $node );
			}

			if ( $matches ) {
				break;
			}
		}

		return $crawler;
	}

	/**
	 * Wrap all the elements in the HTML instance with a specified wrapping element.
	 *
	 * @throws InvalidArgumentException If the wrapping element is invalid.
	 *
	 * @param string|HTML|DOMNode $element The wrapping element to use. Can be a string, a HTML instance, or a DOMNode.
	 */
	public function wrap( string|HTML|DOMNode $element ): static {
		$element = $this->resolve_mixed_argument( $element );

		if ( is_null( $element ) ) {
			throw new InvalidArgumentException( 'Invalid wrapping element provided.' );
		}

		// Bail out if there are no nodes to wrap.
		if ( ! $this->has_nodes() ) {
			return $this;
		}

		foreach ( $this as $node ) {
			if ( ! $node instanceof DOMElement ) {
				continue;
			}

			$new_node = static::import_new_node( $element, $node );

			$node->parentNode?->insertBefore( $new_node, $node );
			$new_node->appendChild( $node );
		}

		return $this;
	}

	/**
	 * Wrap all elements that match the HTML instance with a single wrapping
	 * element.
	 *
	 * Example:
	 *
	 * Before wrapping:
	 *
	 * ```php
	 * <div>
	 *   <h3>Title</h3>
	 *   <p>Content</p>
	 *   <p>More content</p>
	 *   <p>Even more content</p>
	 * </div>
	 * ```
	 *
	 * After wrapping 'p' elements with `<div class="wrapper">`:
	 *
	 * ```php
	 * <div>
	 *  <h3>Title</h3>
	 *  <div class="wrapper">
	 *      <p>Content</p>
	 *      <p>More content</p>
	 *      <p>Even more content</p>
	 *  </div>
	 * </div>
	 * ```
	 *
	 * @throws InvalidArgumentException If the wrapping element is invalid.
	 * @param string|HTML|DOMNode $element
	 */
	public function wrap_all( string|HTML|DOMNode $element ): static {
		$element = $this->resolve_mixed_argument( $element );

		if ( is_null( $element ) ) {
			throw new InvalidArgumentException( 'Invalid wrapping element provided.' );
		}

		// Bail out if there are no nodes to wrap.
		if ( ! $this->has_nodes() ) {
			return $this;
		}

		$parent = $this->getNode( 0 )?->parentNode;

		if ( $parent instanceof DOMDocument ) {
			throw new InvalidArgumentException( 'Cannot wrap nodes that are direct children of a DOMDocument' );
		}

		foreach ( $this as $node ) {
			if ( $node->parentNode !== $parent ) {
				throw new InvalidArgumentException( 'Nodes to be wrapped with wrap_all() must all have the same parent' );
			}
		}

		// Create a new wrapping element and insert it before the first node.
		$new_node = static::import_new_node( $element, $this->getNode( 0 ) ); // @phpstan-ignore-line argument.type
		$parent->insertBefore( $new_node, $this->getNode( 0 ) ); // @phpstan-ignore-line argument.type

		foreach ( $this as $node ) {
			$new_node->appendChild( $node );
		}

		// Remove the wrapping element if it has no child nodes after wrapping.
		if ( $parent instanceof \DOMNode && ! $parent->hasChildNodes() ) {
			$parent->parentNode?->removeChild( $parent );
		}

		return $this;
	}

	/**
	 * Wrap all the inner content of the elements in the HTML instance with a
	 * specified wrapping element.
	 *
	 * @throws InvalidArgumentException If the wrapping element is invalid.
	 *
	 * @param string|HTML|DOMNode $element The wrapping element to use.
	 */
	public function wrap_inner( string|HTML|DOMNode $element ): static {
		$element = $this->resolve_mixed_argument( $element );

		if ( is_null( $element ) ) {
			throw new InvalidArgumentException( 'Invalid wrapping element provided.' );
		}

		foreach ( $this as $node ) {
			( new static( $node->childNodes ) )->wrap_all( $element );
		}

		return $this;
	}

	/**
	 * Empty the content of all elements in the HTML instance.
	 *
	 * This method sets the nodeValue of each element to an empty string,
	 * effectively removing all child nodes and text content.
	 */
	public function empty(): static {
		foreach ( $this as $node ) {
			$node->nodeValue = '';
		}

		return $this;
	}

	/**
	 * Check if the HTML instance has any nodes.
	 */
	public function has_nodes(): bool {
		return $this->count() > 0;
	}

	/**
	 * Dump the HTML representation of the HTML instance.
	 */
	public function dump(): static {
		dump( $this->to_html() );

		return $this;
	}

	/**
	 * Dump the HTML representation of the HTML instance and stop execution.
	 */
	public function dd(): never {
		dd( $this->to_html() );
	}

	/**
	 * Check if the first node in the HTML instance is an HTML document.
	 */
	public function is_html_document(): bool {
		$node = $this->getNode( 0 );
		return ( $node instanceof \DOMElement
			&& $node->ownerDocument instanceof \DOMDocument
			&& $node->ownerDocument->documentElement === $node
			&& $node->nodeName === 'html'
		);
	}

	/**
	 * Get ownerDocument of the first element.
	 */
	public function get_dom_document(): ?DOMDocument {
		$node = $this->getNode( 0 );

		return $node instanceof \DOMElement && $node->ownerDocument instanceof DOMDocument
			? $node->ownerDocument
			: null;
	}

	/**
	 * Adds HTML/XML content to the HtmlPageHTML object (but not to the DOM of an already attached node).
	 *
	 * Function overridden from HTML because HTML fragments are always added as complete documents there
	 *
	 * @param string      $content A string to parse as HTML/XML.
	 * @param null|string $type    The content type of the string.
	 */
	#[Override]
	public function addContent( string $content, ?string $type = null ): void {
		if ( empty( $type ) ) {
			$type = 'text/html;charset=UTF-8';
		}

		// The string contains no <html> Tag => no complete document but an HTML fragment.
		if ( str_starts_with( $type, 'text/html' ) && ! preg_match( '/<html\b[^>]*>/i', $content ) ) {
			$this->add_html_fragment( $content );
		} else {
			parent::addContent( $content, $type );
		}
	}

	/**
	 * Adds an HTML fragment to the HTML object.
	 *
	 * This method parses the provided HTML fragment and appends its child nodes
	 * to the root of the current HTML instance.
	 *
	 * @param string $content The HTML fragment to add.
	 * @param string $charset The character set to use for parsing (default: 'UTF-8').
	 */
	public function add_html_fragment( string $content, string $charset = 'UTF-8' ): void {
		$document                     = new \DOMDocument( '1.0', $charset );
		$document->preserveWhiteSpace = false;

		$root      = $document->appendChild( $document->createElement( self::FRAGMENT_ROOT_TAGNAME ) );
		$body_node = Helpers::get_body_node_from_html_fragment( $content, $charset );

		foreach ( $body_node->childNodes as $child ) {
			$inode = $root->appendChild( $document->importNode( $child, true ) );

			$this->addNode( $inode );
		}
	}

	/**
	 * Import a new node into the existing document and replace the existing node.
	 *
	 * @param DOMNode $new_node The new node to import.
	 * @param DOMNode $existing_node The existing node to replace.
	 * @param bool    $clone Whether to clone the new node if it belongs to the same document.
	 */
	protected static function import_new_node( DOMNode $new_node, DOMNode $existing_node, bool $clone = false ): DOMNode {
		if ( $existing_node->ownerDocument instanceof \DOMDocument && $new_node->ownerDocument !== $existing_node->ownerDocument ) {
			$existing_node->ownerDocument->preserveWhiteSpace = false;

			$new_node = $existing_node->ownerDocument->importNode( $new_node, true );
		} elseif ( $clone ) {
			$new_node = $new_node->cloneNode( true );
		}

		return $new_node;
	}

	/**
	 * Resolve the wrapping element to a DOMNode.
	 *
	 * @param string|HTML|DOMNode $wrapping_element The wrapping element to resolve.
	 */
	protected function resolve_mixed_argument( string|HTML|DOMNode $wrapping_element ): ?DOMNode {
		if ( is_string( $wrapping_element ) ) {
			return ( new static( $wrapping_element ) )->getNode( 0 );
		}

		if ( $wrapping_element instanceof HTML ) {
			return $wrapping_element->getNode( 0 );
		}

		return $wrapping_element;
	}
}
