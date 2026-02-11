<?php
/**
 * Uri class file
 *
 * @package mantle
 */

namespace Mantle\Support;

use Mantle\Contracts\Support\Htmlable;
use Mantle\Support\Traits\Conditionable;
use Mantle\Support\Traits\Macroable;
use Mantle\Support\Traits\Tappable;
use League\Uri\Contracts\UriInterface;
use League\Uri\Uri as LeagueUri;
use Symfony\Component\HttpFoundation\RedirectResponse;
use SensitiveParameter;
use Stringable;

use function Mantle\Support\Helpers\data_set;

/**
 * Uri Support Class
 *
 * This class provides a fluent interface for working with URIs, including
 * methods for retrieving and manipulating various components of a URI such as
 * scheme, host, path, query parameters, and fragments. It also supports
 * creating URIs from the current request, merging query parameters, and
 * generating redirect responses.
 */
class Uri implements Htmlable, Stringable {
	use Conditionable;
	use Macroable;
	use Tappable;

	/**
	 * The URI instance.
	 */
	protected UriInterface $uri;

	/**
	 * Create a new parsed URI instance from the current request.
	 */
	public static function current(): static {
		return new static( LeagueUri::fromServer( $_SERVER ) );
	}

	/**
	 * Create a new parsed URI instance.
	 *
	 * @param UriInterface|Stringable|string $uri The URI to parse. If a Stringable object is provided, it will be converted to a string.
	 */
	public function __construct( UriInterface|Stringable|string $uri = '' ) {
		$this->uri = $uri instanceof UriInterface ? $uri : LeagueUri::new( (string) $uri );
	}

	/**
	 * Create a new URI instance.
	 *
	 * @param UriInterface|Stringable|string $uri The URI to parse. If a Stringable object is provided, it will be converted to a string.
	 */
	public static function of( UriInterface|Stringable|string $uri = '' ): static {
		return new static( $uri );
	}

	/**
	 * Get the URI's scheme.
	 */
	public function scheme(): ?string {
		return $this->uri->getScheme();
	}

	/**
	 * Get the user from the URI.
	 *
	 * @param bool $with_password Whether to include the password in the returned string.
	 */
	public function user( bool $with_password = false ): ?string {
		return $with_password
			? $this->uri->getUserInfo()
			: $this->uri->getUsername();
	}

	/**
	 * Get the password from the URI.
	 */
	public function password(): ?string {
		return $this->uri->getPassword();
	}

	/**
	 * Get the URI's host.
	 */
	public function host(): ?string {
		return $this->uri->getHost();
	}

	/**
	 * Get the URI's port.
	 */
	public function port(): ?int {
		return $this->uri->getPort();
	}

	/**
	 * Get the URI's path always with a leading slash.
	 *
	 * Empty or missing paths are returned as a single "/".
	 */
	public function path(): ?string {
		$path = $this->uri->getPath();

		return empty( $path ) ? '/' : $path;
	}

	/**
	 * Get the URI's path segments.
	 *
	 * Empty or missing paths are returned as an empty collection.
	 */
	public function path_segments(): Collection {
		$path = trim( (string) $this->path(), '/' );

		return empty( $path ) ? new Collection() : new Collection( explode( '/', $path ) );
	}

	/**
	 * Get the URI's query string.
	 */
	public function query(): Uri_Query_String {
		return new Uri_Query_String( $this );
	}

	/**
	 * Get the URI's fragment.
	 */
	public function fragment(): ?string {
		return $this->uri->getFragment();
	}

	/**
	 * Specify the scheme of the URI.
	 *
	 * @param Stringable|string $scheme The scheme to set in the URI.
	 */
	public function with_scheme( Stringable|string $scheme ): static {
		return new static( $this->uri->withScheme( $scheme ) );
	}

	/**
	 * Specify the user and password for the URI.
	 *
	 * @param Stringable|string|null $user The user to set in the URI.
	 * @param Stringable|string|null $password The password to set in the URI.
	 */
	public function with_user( Stringable|string|null $user, #[SensitiveParameter] Stringable|string|null $password = null ): static {
		return new static( $this->uri->withUserInfo( $user, $password ) );
	}

	/**
	 * Specify the host of the URI.
	 *
	 * @param Stringable|string $host The host to set in the URI.
	 */
	public function with_host( Stringable|string $host ): static {
		return new static( $this->uri->withHost( $host ) );
	}

	/**
	 * Specify the port of the URI.
	 *
	 * @param int|null $port The port to set in the URI. If null, the port will be removed.
	 */
	public function with_port( ?int $port ): static {
		return new static( $this->uri->withPort( $port ) );
	}

	/**
	 * Specify the path of the URI.
	 *
	 * @param Stringable|string $path The path to set in the URI.
	 */
	public function with_path( Stringable|string $path ): static {
		return new static( $this->uri->withPath( Str::start( (string) $path, '/' ) ) );
	}

	/**
	 * Merge new query parameters into the URI.
	 *
	 * @param array<string, mixed> $query
	 * @param bool                 $merge Whether to merge the new query parameters with the
	 *                                    existing ones. If true, existing parameters will be
	 *                                    preserved and new ones will be added. If false, the
	 *                                    existing query parameters will be replaced with the new
	 *                                    ones.
	 */
	public function with_query( array $query, bool $merge = true ): static {
		if ( $merge ) {
			$merged_query = $this->query()->all();

			foreach ( $query as $key => $value ) {
				data_set( $merged_query, $key, $value );
			}

			$new_query = $merged_query;
		} else {
			$new_query = [];

			foreach ( $query as $key => $value ) {
				data_set( $new_query, $key, $value );
			}
		}

		return new static( $this->uri->withQuery( Arr::query( $new_query ) ?: null ) );
	}

	/**
	 * Merge new query parameters into the URI if they are not already in the query string.
	 *
	 * @param array<string, mixed> $query
	 */
	public function with_query_if_missing( array $query ): static {
		$current = $this->query();

		foreach ( array_keys( $query ) as $key ) {
			if ( ! $current->missing( $key ) ) {
				Arr::forget( $query, $key );
			}
		}

		return $this->with_query( $query );
	}

	/**
	 * Push a value onto the end of a query string parameter that is a list.
	 *
	 * @param string $key The key of the query parameter.
	 * @param mixed  $value The value to push onto the query parameter.
	 */
	public function push_onto_query( string $key, mixed $value ): static {
		$current = data_get( $this->query()->all(), $key );

		$values = Arr::wrap( $value );

		return $this->with_query( [
			$key => match ( true ) {
				is_array( $current ) && array_is_list( $current ) => array_values( array_unique( [ ...$current, ...$values ] ) ),
				is_array( $current ) => [ ...$current, ...$values ],
				! is_null( $current ) => [ $current, ...$values ],
				default => $values,
			},
		] );
	}

	/**
	 * Remove the given query parameters from the URI.
	 *
	 * @param array<string, string>|string|null $keys
	 */
	public function without_query( array|string|null $keys = null ): static {
		if ( is_null( $keys ) ) {
			return $this->replace_query( [] );
		}

		return $this->replace_query( Arr::except( $this->query()->all(), $keys ) );
	}

	/**
	 * Specify new query parameters for the URI.
	 *
	 * @param array<string, string> $query
	 */
	public function replace_query( array $query ): static {
		return $this->with_query( $query, merge: false );
	}

	/**
	 * Specify the fragment of the URI.
	 *
	 * @param string $fragment The fragment to set in the URI.
	 */
	public function with_fragment( string $fragment ): static {
		return new static( $this->uri->withFragment( $fragment ) );
	}

	/**
	 * Create a redirect HTTP response for the given URI.
	 *
	 * @param  int                   $status HTTP status code for the redirect. Default is 302 (Found).
	 * @param  array<string, string> $headers Additional headers to include in the response.
	 */
	public function redirect( int $status = 302, array $headers = [] ): RedirectResponse {
		return new RedirectResponse( $this->value(), $status, $headers );
	}

	/**
	 * Create an HTTP response that represents the object.
	 *
	 * @param mixed $request The request object.
	 */
	public function to_response( $request ): RedirectResponse {
		return new RedirectResponse( $this->value() );
	}

	/**
	 * Get content as a string of HTML.
	 */
	public function to_html(): string {
		return $this->value();
	}

	/**
	 * Get the decoded string representation of the URI.
	 */
	public function decode(): string {
		if ( empty( $this->query()->to_array() ) ) {
			return $this->value();
		}

		return Str::replace( Str::after( $this->value(), '?' ), $this->query()->decode(), $this->value() );
	}

	/**
	 * Get the string representation of the URI.
	 */
	public function value(): string {
		return (string) $this;
	}

	/**
	 * Determine if the URI is currently an empty string.
	 */
	public function is_empty(): bool {
		return trim( $this->value() ) === '';
	}

	/**
	 * Dump the string representation of the URI.
	 *
	 * @param  mixed ...$args
	 * @return $this
	 */
	public function dump( ...$args ): static {
		dump( $this->value(), ...$args );

		return $this;
	}

	/**
	 * Get the underlying URI instance.
	 */
	public function get_uri(): UriInterface {
		return $this->uri;
	}

	/**
	 * Get the string representation of the URI.
	 */
	public function __toString(): string {
		return $this->uri->toString();
	}
}
