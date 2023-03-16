# Visitors
Visitors plugin for WP community.

## Plugin configuration

By default, this plugin tracks visits of `page` and `post` post types and `category` and `post_tag` taxonomy terms. Visits are tracked only for anonymous users or users logged in with `subscriber` role.

You can modify default configuration using filters. To do so, place this code to functions.php:

```php
// Change tracked post types
add_filter( 'naswp_visitors_cpt', function( array $defaultTypes ) {
	// Add posts of 'book' post type to be tracked
	$defaultTypes[] = 'book';
	return $defaultTypes;

	// Remove 'page' post type from tracked
	return array_filter( $defaultTypes, fn( string $type ) => $type !== 'page' );

	// Or just return an array of post type slugs
	return [ 'page', 'post', 'book' ];
} );

// Change tracked taxonomies
add_filter( 'naswp_visitors_tax', function( array $defaultTaxonomies ) {
	// Add terms of 'book_author' to be tracked
	$defaultTaxonomies[] = 'book_author';
	return $defaultTaxonomies;

	// Remove 'post_tag' from tracked taxonomies
	return array_filter( $defaultTaxonomies, fn( string $tax ) => $tax !== 'post_tag' );

	// Or just return an array of taxonomy slugs
	return [ 'category', 'post_tag', 'book_author' ];
} );

// Change tracked user roles (anonymous users are always tracked)
add_filter( 'naswp_visitors_roles', function( array $defaultRoles ) {
	// Add 'admin' role to be tracked
	$newRoles = array_merge( $defaultRoles, [ 'admin' ] );

	// Remove 'subscriber' from tracked roles
	$newRoles = array_filter( $defaultRoles, fn( string $role ) => $role !== 'subscriber' );

	return $newRoles;
} );

```

## How to get number of visitors

Visitor counts are stored in post meta / term meta named by default as follows:

- `naswp_visitors_total` for total count of visits
- `naswp_visitors_yearly` for visits in the past 12 months
- `naswp_visitors_monthly` for visits in the past 30 days
- `naswp_visitors_daily` for visits in the past 24 hours

Meta values are recalculated whenever a visitor comes to a page or term and either stays for at least 6 seconds or perform any interaction (mouse move, click, touch, scroll). That's why the values in post meta **might not be accurate** - they are only relevant at the time of the last visit. For example, if there isn't any visit in current month, meta value will still hold counts relevant for previous month, because there wasn't any chance to recalculate it.

For this reason, use following code to get appropriate visit counts. Following code get the post meta value and additionaly checks it's relevance.

```php
$visitors = new NasWP_Visitors_Post( 1 ); // For post with ID = 1
$visitors = new NasWP_Visitors_Term( 1 ); // For term with term_id = 1

// For past 24 hours
echo $visitors->get_daily();

// For past 30 days
echo $visitors->get_monthly();

// For past 12 months
echo $visitors->get_yearly();

// Total count
echo $visitors->get_total();
```

## How to order WP Query according to to number of visitors

Plugin adds the ability to order WP query and Term query by number of visits in required time interval. Use one of the following constants as orderby value in query arguments:

- `NASWP_VISITORS_TOTAL` for order by total number of visits
- `NASWP_VISITORS_YEARLY` for order by visits in the past 12 months
- `NASWP_VISITORS_MONTHLY` for order by visits in the past 30 days
- `NASWP_VISITORS_DAILY` for order by visits in the past 24 hours

For example:

```php
// For posts, same for query_posts()
$posts = get_posts( [
	'orderby' => NASWP_VISITORS_TOTAL,
	'order' => 'DESC',
	// ...
] );

// For taxonomy terms
$terms = get_terms( [
	'orderby' => NASWP_VISITORS_MONTHLY,
	'order' => 'DESC',
	// ...
] );
```

Order takes into account the time-validation of visitor count as mentioned above.

> There are two limitations:
> - You can only use **one orderby argument**. Passing orderby as array won't work.
> - You **can't suppress filters** in the query using `'suppress_filters'` argument.
