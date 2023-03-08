<?php

/**
 * Tests
 *
 * To run these tests:
 * 1. Install plugin to any local WordPress instance
 * 2. Create some post with enabled views tracking, note it's ID
 * 3. Set post ID to constant below
 * 4. Run this file in CLI:
 * 		php test/model.php
 */


// (new NasWP_Visitors_Post( 1 ))->reset_data();
// (new NasWP_Visitors_Post( 9 ))->reset_data();

// (new NasWP_Visitors_Post( 1, strtotime( '07.03.2023 20:00:00' ) ))->track_visit();
// (new NasWP_Visitors_Post( 1, strtotime( '08.03.2023 20:00:00' ) ))->track_visit();

// (new NasWP_Visitors_Post( 9, strtotime( '07.03.2023 20:00:00' ) ))->track_visit();
// // (new NasWP_Visitors_Post( 9, strtotime( '08.03.2023 20:00:00' ) ))->track_visit();




// Reset data
$model = new NasWP_Visitors_Post( NASWP_TEST_POST_ID );
$model->reset_data();

// Hit 1 (initial state)
$time1 = strtotime( '30.12.2020 12:00:00' );
__naswp_echo("1. Adding initial hit " . date( 'c', $time1 ) );

$model = new NasWP_Visitors_Post( NASWP_TEST_POST_ID, $time1 );
$model->track_visit();

__naswp_check_eq( $model->get_daily(), 1, "Daily meta" );
__naswp_check_eq( $model->get_monthly(), 1, "Monthly meta" );
__naswp_check_eq( $model->get_yearly(), 1, "Yearly meta" );
__naswp_check_eq( $model->get_total(), 1, "Total meta" );

__naswp_check_eq( count( $model->get_daily_data(30) ), 1, "Daily view count" );
__naswp_check_view( $model->get_daily_data(30)[0], 1, $time1, "Daily view" );

__naswp_check_eq( count( $model->get_monthly_data(12) ), 1, "Monthly view count" );
__naswp_check_view( $model->get_monthly_data(12)[0], 1, $time1, "Monthly view" );

__naswp_check_eq( count( $model->get_yearly_data() ), 1, "Yearly view count" );
__naswp_check_view( $model->get_yearly_data()[0], 1, $time1, "Yearly view" );

__naswp_echo("-> OK", 2);


// Hit 2 (day, month and year overlapped)
$time2 = strtotime( '01.01.2021 12:00:00' );
__naswp_echo("2. Adding new hit - different day, next month and next year: " . date( 'c', $time2 ) );

$model = new NasWP_Visitors_Post( NASWP_TEST_POST_ID, $time2 );
$model->track_visit();

__naswp_check_eq( $model->get_daily(), 1, "Daily meta" );
__naswp_check_eq( $model->get_monthly(), 2, "Monthly meta" );
__naswp_check_eq( $model->get_yearly(), 2, "Yearly meta" );
__naswp_check_eq( $model->get_total(), 2, "Total meta" );

__naswp_check_eq( count( $model->get_daily_data(30) ), 2, "Daily view count" );
__naswp_check_view( $model->get_daily_data(30)[0], 1, $time2, "First daily view" );
__naswp_check_view( $model->get_daily_data(30)[1], 1, $time1, "Second daily view" );

__naswp_check_eq( count( $model->get_monthly_data(12) ), 2, "Monthly view count" );
__naswp_check_view( $model->get_monthly_data(12)[0], 1, $time2, "First monthly view" );
__naswp_check_view( $model->get_monthly_data(12)[1], 1, $time1, "Second monthly view" );

__naswp_check_eq( count( $model->get_yearly_data() ), 2, "Yearly view count" );
__naswp_check_view( $model->get_yearly_data()[0], 1, $time2, "First yearly view" );
__naswp_check_view( $model->get_yearly_data()[1], 1, $time1, "Second yearly view" );

__naswp_echo("-> OK", 2);


// Hit 3 (same day)
$time3 = strtotime( '01.01.2021 13:00:00' );
__naswp_echo("3. Adding new hit - same day: " . date( 'c', $time3 ) );

$model = new NasWP_Visitors_Post( NASWP_TEST_POST_ID, $time3 );
$model->track_visit();

__naswp_check_eq( $model->get_daily(), 2, "Daily meta" );
__naswp_check_eq( $model->get_monthly(), 3, "Monthly meta" );
__naswp_check_eq( $model->get_yearly(), 3, "Yearly meta" );
__naswp_check_eq( $model->get_total(), 3, "Total meta" );

__naswp_check_eq( count( $model->get_daily_data(30) ), 2, "Daily view count" );
__naswp_check_view( $model->get_daily_data(30)[0], 2, $time3, "First daily view" );
__naswp_check_view( $model->get_daily_data(30)[1], 1, $time1, "Second daily view" );

__naswp_check_eq( count( $model->get_monthly_data(12) ), 2, "Monthly view count" );
__naswp_check_view( $model->get_monthly_data(12)[0], 2, $time3, "First monthly view" );
__naswp_check_view( $model->get_monthly_data(12)[1], 1, $time1, "Second monthly view" );

__naswp_check_eq( count( $model->get_yearly_data() ), 2, "Yearly view count" );
__naswp_check_view( $model->get_yearly_data()[0], 2, $time3, "First yearly view" );
__naswp_check_view( $model->get_yearly_data()[1], 1, $time1, "Second yearly view" );

__naswp_echo("-> OK", 2);


// Hit 4 (after a month)
$time4 = strtotime( '01.02.2021 13:00:00' );
__naswp_echo("4. Adding new hit - after a month: " . date( 'c', $time4 ) );

$model = new NasWP_Visitors_Post( NASWP_TEST_POST_ID, $time4 );
$model->track_visit();

__naswp_check_eq( $model->get_daily(), 1, "Daily meta" );
__naswp_check_eq( $model->get_monthly(), 1, "Monthly meta" );
__naswp_check_eq( $model->get_yearly(), 4, "Yearly meta" );
__naswp_check_eq( $model->get_total(), 4, "Total meta" );

__naswp_check_eq( count( $model->get_daily_data(30) ), 1, "Daily view count" );
__naswp_check_view( $model->get_daily_data(30)[0], 1, $time4, "Daily view" );

__naswp_check_eq( count( $model->get_monthly_data(12) ), 3, "Monthly view count" );
__naswp_check_view( $model->get_monthly_data(12)[0], 1, $time4, "First monthly view" );
__naswp_check_view( $model->get_monthly_data(12)[1], 2, $time3, "Second monthly view" );
__naswp_check_view( $model->get_monthly_data(12)[2], 1, $time1, "Third monthly view" );

__naswp_check_eq( count( $model->get_yearly_data() ), 2, "Yearly view count" );
__naswp_check_view( $model->get_yearly_data()[0], 3, $time4, "First yearly view" );
__naswp_check_view( $model->get_yearly_data()[1], 1, $time1, "Second yearly view" );

__naswp_echo("-> OK", 2);


// Hit 5 (after a year)
$time5 = strtotime( '01.02.2022 13:00:00' );
__naswp_echo("5. Adding new hit - after a year: " . date( 'c', $time5 ) );

$model = new NasWP_Visitors_Post( NASWP_TEST_POST_ID, $time5 );
$model->track_visit();

__naswp_check_eq( $model->get_daily(), 1, "Daily meta" );
__naswp_check_eq( $model->get_monthly(), 1, "Monthly meta" );
__naswp_check_eq( $model->get_yearly(), 1, "Yearly meta" );
__naswp_check_eq( $model->get_total(), 5, "Total meta" );

__naswp_check_eq( count( $model->get_daily_data(30) ), 1, "Daily view count" );
__naswp_check_view( $model->get_daily_data(30)[0], 1, $time5, "Daily view" );

__naswp_check_eq( count( $model->get_monthly_data(12) ), 1, "Monthly view count" );
__naswp_check_view( $model->get_monthly_data(12)[0], 1, $time5, "First monthly view" );

__naswp_check_eq( count( $model->get_yearly_data() ), 3, "Yearly view count" );
__naswp_check_view( $model->get_yearly_data()[0], 1, $time5, "First yearly view" );
__naswp_check_view( $model->get_yearly_data()[1], 3, $time4, "Second yearly view" );
__naswp_check_view( $model->get_yearly_data()[2], 1, $time1, "Third yearly view" );

__naswp_echo("-> OK", 2);


__naswp_echo("ALL TESTS PASSED", 2);
exit( 0 );