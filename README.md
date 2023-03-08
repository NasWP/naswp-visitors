# visitors-naswp
Visitors plugin for WP community.


## How to get number of visitors

Visitor counts are stored in post_meta named by default as follows:

- `naswp_visitors_total` for total count of visits
- `naswp_visitors_yearly` for visits in the past 12 months
- `naswp_visitors_monthly` for visits in the past 30 days
- `naswp_visitors_daily` for visits in the past 24 hours

Values in these post_meta are recalculated whenever a visitor comes to a page and either stays for at least 6 seconds or perform any interaction (mouse move, click, touch, scroll). That's why the values in post meta **might not be accurate** - they are only relevant at the time the page is visited. For example, if there isn't any visit in current month, postmeta will still hold counts relevant for previous month, because there wasn't any chance to recalculate them.

For this reason, use following code to get appropriate visit counts. Following code get the post meta value and additionaly checks it's relevance.

```php
$postId = 1;
$visitors = new NasWP_Visitors_Post( $postId );

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

Plugin adds the ability to order WP query by number of visits in required time interval. Use one of the following constants as orderby value in query arguments:

- `NASWP_VISITORS_TOTAL` for order by total number of visits
- `NASWP_VISITORS_YEARLY` for order by visits in the past 12 months
- `NASWP_VISITORS_MONTHLY` for order by visits in the past 30 days
- `NASWP_VISITORS_DAILY` for order by visits in the past 24 hours

For example:

```php
query_posts( [
	'orderby' => NASWP_VISITORS_TOTAL,
	'order' => 'DESC',
	// ...
] );
```

Order takes into account the time-validation of visitor count as mentioned above.

> There are two limitations:
> - You can only use one orderby argument. Passing orderby as array won't work.
> - You can't suppress filters in the query using `'suppress_filters'` argument.
