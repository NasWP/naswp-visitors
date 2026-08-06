# Visitors
Plugin for tracking site traffic without Cookies, localStorage or sessionStorage.

**Download here -> [naswp-visitors.zip](https://github.com/NasWP/naswp-visitors/releases/latest/download/naswp-visitors.zip)** and upload in Plugin installations.

## How does it work

Plugin doesn't use cookies, session or local storage to identify user. It uses the browser cache, specifically `$_SERVER['HTTP_IF_MODIFIED_SINCE']`, where it stores the generated value, so **this plugin does not store any personal information**. If the value isn't store in this server variable, the plugin increase visit counts and stores the information to the cache. So if the same user visits the same page again, the plugin will not increase the number of visits due to the information stored in the browser cache, of course until the browser cache is cleared.

Visits are tracked using AJAX script to prevent any problems caused by caching plugins. Additionaly, visit isn't tracked immediately, user has to either spend 6 seconds on the page or perform any interaction, such as:

- move with mouse
- scroll the page
- touch the screen on mobile devices
- hit any key on keyboard

*This plugin is not a replacement for Google Analytics and similar tools. The measurements are not completely accurate, but from our perspective it is the best way to measure traffic without storing personal data and having to use a cookie bar.*

### Tracking endpoint performance

Since version 1.2.0 the tracking request goes to a standalone endpoint (`public/track.php`) which boots WordPress with the `SHORTINIT` constant - without plugins, theme and most of core. Compared to the previous `admin-ajax.php` route (full WordPress boot for every hit) this cuts the CPU cost of a tracking hit roughly by two orders of magnitude.

The request is secured by a stateless HMAC token derived from the site's secret keys, so it keeps working even on pages served from a long-lived page cache (WP nonces expire after 24 hours, which used to silently stop tracking on cached pages).

If the lightweight endpoint fails - for example when security rules block direct access to PHP files inside `wp-content/plugins/`, or on multisite - the script automatically falls back to the original `admin-ajax.php` route. Tracking keeps working, only without the performance benefit.

If you use a CDN with a "cache everything" rule on `/wp-content/*`, exclude `wp-content/plugins/naswp-visitors/public/track.php` from it, otherwise the CDN would serve the tracking response to other visitors and their visits would not be counted.

## Plugin configuration

By default, this plugin tracks visits of `page` and `post` post types and `category` and `post_tag` taxonomy terms. Visits are tracked only for anonymous users or users logged in with `subscriber` role.

You can modify default configuration using filters. To do so, place this code to functions.php:

```php
// Change tracked post types
add_filter( 'naswp_visitors_cpt', function( array $defaultTypes ) {

	// Return an array of post type slugs
	return [ 'page', 'post', 'movie' ];
	
} );

// Change tracked taxonomies
add_filter( 'naswp_visitors_tax', function( array $defaultTaxonomies ) {

	// Return an array of taxonomy slugs
	return [ 'category', 'post_tag', 'movie_genre' ];
	
} );

// Change tracked user roles (anonymous users are always tracked)
add_filter( 'naswp_visitors_roles', function( array $defaultRoles ) {

	// Return an array of roles
	return [ 'subscriber', 'admin' ];
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

To get sum of all visits for all posts or terms, use static methods as follows:

```php
// Total sum of all posts
echo NasWP_Visitors_Post::get_total_sum();

// Total sum of all pages
echo NasWP_Visitors_Post::get_total_sum( [ 'page' ] );

// Total sum of all terms
echo NasWP_Visitors_Term::get_total_sum();

// Total sum of all tags
echo NasWP_Visitors_Term::get_total_sum( [ 'post_tag' ] );
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

## CHANGELOG

### 1.2.0
- Performance: tracking hits now go to a standalone `SHORTINIT` endpoint instead of `admin-ajax.php`, so they no longer boot full WordPress (plugins + theme). Automatic fallback to `admin-ajax.php` when the endpoint is unavailable.
- Fix: tracking on pages served from a page cache older than 24 hours no longer silently stops (stateless HMAC token replaced the expiring WP nonce on the tracking route).
- Fix: tracking responses are now `Cache-Control: private`, so shared caches (CDN, fastcgi_cache) cannot swallow visits of other users.
- Fix: inverted null check in `getLastUpdateLimit()` overwrote the provided reference timestamp.

### 1.1.0
- Maintenance release (version bump only).

### 1.0.2
- Best practise update: replacing our AJAX entry with WP admin-ajax.php

### 1.0.1
- Security fix: [Full Path Disclosure](https://owasp.org/www-community/attacks/Full_Path_Disclosure)

### 1.0.0
- First plugin release with filters for change CPT, TAX and Roles.
