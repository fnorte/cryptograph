<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<title>CRYPTOGraph</title>
	<script defer src="https://www.livecoinwatch.com/static/lcw-widget.js"></script>

	<style>
		#buttons {
			/* width: 50%; */
		}

		#buttons button {
			background-color: #ccc;
			font-weight: 700;
			font-size: 12px;
			padding: 3px;
		}

		#buttons .active {
			background-color: yellow;
		}

		.result {
			font-family: Helvetica, Arial, sans-serif;
			font-size: 12px;
			font-weight: bold;
			padding: 5px 10px;
			display: flex;
			flex-direction: row;
			gap: 5px;
			justify-content: space-between;
		}

		.coin {
			position: relative;
			/* width: 25%; */
		}

		.inv {
			display: inline-block;
			padding-bottom: 10px;
		}

		.tem {
			font-size: 10px;
			color: #666;
			padding-top: 12px;
			display: inline-block;
			position: absolute;
			left: 0;
		}

		.down {
			color: red;
		}

		.up {
			color: green;
		}
	</style>
</head>

<body>
	<div class="livecoinwatch-widget-5" lcw-base="BRL" lcw-color-tx="#000000" lcw-marquee-1="movers" lcw-marquee-2="coins" lcw-marquee-items="10" lcw-platform="BC" ></div>
	<?php
	$t = 'd';
	if ( isset( $_GET['t'] ) ) {
		$t = $_GET['t'];
	}


		$currency = array(
			'UNI' => 'SXP',
			'SXP' => 'BNB',
			'BNB' => 'BTC',
			'BTC' => 'LTC',
			'LTC' => 'BNB',
			'ETH' => 'SXP',
			'SHIB' => 'XRP',
			'LINK' => 'UNI',
			'SOL' => 'BTC',
		);

	// $invest['BNB']['tem'] = 0.04506704;
	// $invest['BNB']['inv'] = 63.40;

	$invest['UNI']['tem'] = 2.76426789;
	$invest['UNI']['inv'] = 100;

	$invest['SXP']['tem'] = 54.07295111;
	$invest['SXP']['inv'] = 100.01;

	$invest['LTC']['tem'] = 0.26979554;
	$invest['LTC']['inv'] = 100;

	$invest['BTC']['tem'] = 0.00094256;
	$invest['BTC']['inv'] = 200;


	$coins = [];
	foreach ( $invest as $coin => $data ) {
		$coins[] = $coin;
	}

	$api_key = 'b3d7d1ae-f0b0-4219-8388-f4fb4025d899';
	$data = json_encode( array( 'codes' => $coins, 'currency' => 'BRL', 'sort' => 'rank', 'order' => 'ascending', 'offset' => 0, 'limit' => 0, 'meta' => false ), JSON_PRETTY_PRINT );
	$context_options = array(
		'http' => array(
			'method' => 'POST',
			'header' => "Content-type: application/json\r\n"
				. "x-api-key: " . $api_key . "\r\n",
			'content' => $data
		)
	);
	$context = stream_context_create( $context_options );
	$fp = fopen( 'https://api.livecoinwatch.com/coins/map', 'r', false, $context );
	$head_data = stream_get_contents( $fp );
	$head_data = json_decode( $head_data, true );
	?>
	<div style=" display: flex; flex-direction: column;">
		<div class="result">
			<div id="buttons">
				<?php
				$times = array(
					'h' => '1H',
					'd' => '24H',
					'w' => '7D',
					'm' => '30D',
					'q' => '90D',
					'y' => '1Y',
				);
				foreach ( $times as $k => $v ) {
					echo '
					<button name="t" value="' . $k . '"' . ( $t == $k ? ' class="active"' : '' ) . '>' . $v . '</button>';
				}
				?>
			</div>
			<?php
			$investimentos = [];
			$tt_inv = 0;
			$tt_win = 0;
			foreach ( $invest as $coin => $data ) {
				$rate = 1;
				$tenho = 0;
				$varia = 0;
				$coin = strtoupper( $coin );

				foreach ( $head_data as $head ) {
					if ( $head['code'] == $coin ) {
						$rate = $head['rate'];
						$tenho = $invest[ $coin ]['tem'] * $rate;
						$tt_inv += $invest[ $coin ]['inv'];
						$tt_win += $tenho;
						$varia = ( $tenho - $invest[ $coin ]['inv'] ) * 100 / $invest[ $coin ]['inv'];

					}
				}

				$investimentos[] = [ 
					'coin' => $coin,
					'varia' => $varia,
					'tenho' => $tenho,
					'inv' => $invest[ $coin ]['inv'],
					'tem' => $invest[ $coin ]['tem'],
					'data' => $data,
				];
			}


			function sortByOrder( $a, $b ) {
				if ( $a['varia'] < $b['varia'] ) {
					return 1;
				} elseif ( $a['varia'] > $b['varia'] ) {
					return -1;
				}
				return 0;
			}

			usort( $investimentos, 'sortByOrder' );

			$investimentos[] = [ 
				'coin' => 'TOTAL',
				'varia' => ( $tt_win - $tt_inv ) * 100 / $tt_inv,
				'tenho' => $tt_win,
				'inv' => $tt_inv,
				'tem' => 0,
				'data' => [ 'inv' => $tt_inv, 'tem' => ( $tt_win - $tt_inv ) ],
			];
			// echo "<pre>";
			// var_dump( $investimentos );
			// echo "</pre>";
			
			foreach ( $investimentos as $data ) {
				echo '
					<div class="coin ' . $data['coin'] . '">
						<span class="name">' . $data['coin'] . '</span>
						<span class="inv';
				if ( $data['varia'] < 0 ) {
					echo ' down';
				} else {
					echo ' up';
				}
				echo '">R$' . number_format( $data['tenho'], 2, ',', '.' ) . '</span>
						<span class="tem">R$' . $data['data']['inv'] . ' - ' . $data['data']['tem'] . '</span>
						<span class="saldo';
				if ( $data['varia'] < 0 ) {
					echo ' down';
				} else {
					echo ' up';
				}
				echo '" id="invest_' . $data['coin'] . '">(' . number_format( $data['varia'], 2, ',', '.' ) . '%)</span>
					</div>';

			}
			?>
		</div>
		<?php
		$beg_block = '
		<div style="display: flex; flex-direction: row; justify-content: space-around;">';
		$end_block = '
		</div>';


		$c = 0;

		echo $beg_block;
		foreach ( $currency as $code => $sec ) {
			$c++;
			echo '
			<div id="' . $code . '" class="livecoinwatch-widget-1" lcw-coin="' . $code . '" lcw-base="BRL" lcw-secondary="' . $sec . '" lcw-period="' . $t . '"
			lcw-color-tx="#ffffff" lcw-color-pr="#58c7c5" lcw-color-bg="#1f2434" lcw-border-w="1"></div>';
			if ( $c % 3 === 0 ) {
				echo $end_block;
				echo $beg_block;
			}
		}
		echo $end_block;
		?>
	</div>
	<script type="text/javascript">
		[].forEach.call(
			document.getElementsByTagName('button'),
			function (b) {
				b.addEventListener('click', function (ev) {
					window.location.href = '?t=' + ev.target.value;
				});
			}
		);
	</script>

</body>

</html>