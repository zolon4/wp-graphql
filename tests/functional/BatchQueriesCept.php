<?php

$I = new FunctionalTester( $scenario );
$I->wantTo( 'Test batch queries' );

// There's a funky issue where WordPress is trying to update during tests
// and goes into maintenance mode and fails the tests.
// This is an attempt to wait for it to pass.
sleep( 2 );

$options = [
	'batch_queries_enabled' => 'on'
];

$I->haveOptionInDatabase( 'graphql_general_settings', $options );

$I->havePostInDatabase( [
	'post_type'    => 'post',
	'post_status'  => 'publish',
	'post_title'   => "test post",
	'post_content' => "test content"
] );

$I->haveHttpHeader( 'Content-Type', 'application/json' );

$I->sendPost( 'http://localhost/graphql', json_encode([
	[
		'query' => '{posts{nodes{id,title}}}',
	],
	[
		'query' => '{posts{nodes{id,uri}}}'
	]
]));

$I->seeResponseCodeIs( 200 );
$I->seeResponseIsJson();
$response       = $I->grabResponse();
$response_array = json_decode( $response, true );

$I->assertArrayNotHasKey( 'errors', $response_array[0], 'Batch Queries are enabled and the first query should be valid' );
$I->assertNotEmpty( $response_array[0]['data'], 'Batch Queries are enabled and the first query should be valid' );
$I->assertArrayNotHasKey( 'errors', $response_array[1], 'Batch Queries are enabled and the second query should be valid' );
$I->assertNotEmpty( $response_array[1]['data'], 'Batch Queries are enabled and the second query should be valid' );

