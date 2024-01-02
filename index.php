<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<title>CRYPTOGraph</title>
  <meta charset="UTF-8" />
  <meta name="author" content="Troféu Tele Santana">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<script defer src="https://www.livecoinwatch.com/static/lcw-widget.js"></script>

	<link rel='stylesheet' id='theme-css-css' href='assets/css/style.css' type='text/css' media='screen' />
	
</head>

<body>
	<div class="livecoinwatch-widget-5" lcw-base="BRL" lcw-color-tx="#000000" lcw-marquee-1="movers" lcw-marquee-2="coins" lcw-marquee-items="10" lcw-platform="BC" ></div>
	<?php

	$t = 'd';
	if ( isset( $_GET['t'] ) ) {
		$t = $_GET['t'];
	}

		$currency = array(
			'BNB' => '_NFP',
			'_NFP' => 'BNB',
			'XLM' => 'BNB',
			
			'BTC' => 'ETH',
			'ETH' => 'BNB',
			'RUNE' => 'ETH',
			
			'LTC' => 'LINK',
			'LINK' => 'UNI',
			'UNI' => 'LTC',
		);

	$invest['XLM']['tem'] = 76.33684124;
	$invest['XLM']['inv'] = 53.33910391;

	$invest['BNB']['tem'] = 0.10178415;
	$invest['BNB']['inv'] = 155.9844194343906;

	$invest['_NFP']['tem'] = 17.15305559;
	$invest['_NFP']['inv'] = 90.67647665;


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
				'data' => [ 'inv' => $tt_inv, 'tem' => 'R$ ' .number_format( ( $tt_win - $tt_inv ), 2, ',','.') ],
			];
			// echo "<pre>";
			// var_dump( $investimentos );
			// echo "</pre>";
			
			foreach ( $investimentos as $data ) {
				echo '
					<div class="coin ' . $data['coin'] . '">
						<span class="name">' . $data['coin'] . '</span>
						<span class="inv' . color_class( $data['varia'] ) . '">R$' . number_format( $data['tenho'], 2, ',', '.' ) . '</span>
						<span class="tem">R$' . number_format($data['data']['inv'],2,',','.') . ' - ' . $data['data']['tem'] . '</span>
						<span class="saldo' . color_class( $data['varia'] ) . '" id="invest_' . $data['coin'] . '">(' . number_format( $data['varia'], 2, ',', '.' ) . '%)</span>
					</div>';
			}
			?>
		</div>
		<?php
		$beg_block = '
		<div class="widgets">';
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
	<script type="text/javascript" src="assets/js/main.js" id="main_js"></script>

</body>

</html>
<?php
	function color_class( $v ) {
		if ( $v < 0 ) {
			return ' down';
		} else {
			return ' up';
		}
	}
