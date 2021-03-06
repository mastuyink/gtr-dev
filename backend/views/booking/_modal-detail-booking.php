<?php
use yii\helpers\Html;
?>
<div class="row">
<div class="col-md-12">
<p>
<span class="glyphicon glyphicon-check"> <?= $modelBooking->id ?> /</span>
<span> <?= $modelBooking->idTrip->idBoat->idCompany->name ?> </span> /
<span> <?= $modelBooking->idTrip->idRoute->departureHarbor->name." <i class=\"glyphicon glyphicon-arrow-right\"></i> ".$modelBooking->idTrip->idRoute->arrivalHarbor->name ?></span> / 
<span class="fa fa-clock-o"> <?= date('H:i',strtotime($modelBooking->idTrip->dept_time)) ?></span>
</p>
<div class="panel-group material-accordion material-accordion_primary" id="booking-Detail<?= $modelBooking->id ?>">
	<div class="panel panel-default material-accordion__panel material-accordion__panel">
		<div class="panel-heading material-accordion__heading" id="acc1_headingOne">
			<h4 class="panel-title">
				<a data-toggle="collapse" data-parent="#booking-Detail<?= $modelBooking->id ?>" href="#payment<?= $modelBooking->id ?>" class="material-accordion__title"><span class="fa fa-money"></span> Price Detail*</a>
			</h4>
		</div>
		<div id="payment<?= $modelBooking->id ?>" class="panel-collapse collapse in material-accordion__collapse">
			<div class="panel-body">
					<div class="row">
					<div class="col-md-12">
						<table class="table table-stripped">
						<thead>
							<tr class="warning">
								<th width="125px">Type</th>
								<th width="125px">Qty</th>
								<th width="125px">Amount</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$AdultPrice = $modelBooking->idTrip->adult_price;
							$JmlAdult   = count($modelBooking->adultPassengers);
							$Total      = number_format($AdultPrice*$JmlAdult,0);
						?>
							<tr>
								<td width="125px">Adult Tickets @ Rp <?= number_format($AdultPrice,0) ?></td>
								<td width="125px"><?= " X ".$JmlAdult ?></td>
								<td width="125px">Rp <?= $Total ?></td>
							</tr>
						<?php if(!empty($modelBooking->childPassengers)): ?>
							<?php
							$childPrice = $modelBooking->idTrip->child_price;
							$jmlChild   = count($modelBooking->childPassengers);
							$Total      = number_format($AdultPrice*$JmlAdult+$childPrice*$jmlChild,0);
						?>
							<tr>
								<td class="custom-underline" width="125px">Child Tickets @ Rp <?= number_format($childPrice,0) ?></td>
								<td class="custom-underline" width="125px"><?= " X ".$jmlChild ?></td>
								<td class="custom-underline" width="125px">Rp <?= number_format($childPrice*$jmlChild,0) ?></td>
							</tr>
						<?php endif;?>
						<tr>
							<td></td>
							<td></td>
							<td class="font-tebal"><?= "Rp ".$Total ?></td>
						</tr>
						</tbody>
						</table>
						</div>
					</div>
			</div>
		</div>
	</div>
	<div class="panel panel-default material-accordion__panel">
		<div class="panel-heading material-accordion__heading">
			<h4 class="panel-title">
						<a class="collapsed material-accordion__title" data-toggle="collapse" data-parent="#booking-Detail<?= $modelBooking->id ?>" href="#passenger-detail<?= $modelBooking->id ?>"><span class="glyphicon glyphicon-user"></span> Passenger Detail <?= count($modelBooking->affectedPassengers) ?> *</a>
					</h4>
		</div>
		<div id="passenger-detail<?= $modelBooking->id ?>" class="panel-collapse collapse material-accordion__collapse">
			<div class="panel-body">
					<div class="row">
					<div class="col-md-12">
						<table class="table table-stripped">
						<thead>
							<tr class="warning">
								<th scope="row" width="10px">No</th>
								<th width="125px">Name</th>
								<th width="125px">Nationality</th>
								<th width="125px">Type</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach($modelBooking->adultPassengers as $x => $valAdult): $no = $x+1;?>
							<tr>
								<td scope="row"><?= $no ?></td>
								<td><?= $valAdult->name ?></td>
								<td width="125px"><?= $valAdult->idNationality->nationality ?></td>
								<td width="125px">Adult</td>
							</tr>
						<?php endforeach; ?>
						<?php if(!empty($modelBooking->childPassengers)): ?>
						<?php foreach($modelBooking->childPassengers as $xc => $valChild): $noc = $no+$xc+1; ?>
							<tr>
								<td scope="row"><?= $noc ?></td>
								<td><?= $valChild->name ?></td>
								<td width="125px"><?= $valChild->idNationality->nationality ?></td>
								<td width="125px">Child</td>
							</tr>
						<?php endforeach; ?>
						<?php endif;?>
						<?php if(!empty($modelBooking->infantPassengers)): ?>
						<?php foreach($modelBooking->infantPassengers as $xi => $valInfant): $noi = $noc+$xi+1; ?>
							<tr>
								<td scope="row"><?= $noi ?></td>
								<td><?= $valInfant->name ?></td>
								<td width="125px"><?= $valInfant->idNationality->nationality ?></td>
								<td width="125px">Infant</td>
							</tr>
						<?php endforeach; ?>
						<?php endif;?>
						</tbody>
						</table>

						</div>
				</div>
			</div>
		</div>
	</div>
		<div class="panel panel-default material-accordion__panel">
		<div class="panel-heading material-accordion__heading">
			<h4 class="panel-title">
						<a class="collapsed material-accordion__title" data-toggle="collapse" data-parent="#booking-Detail<?= $modelBooking->id ?>" href="#related-booking-<?= $modelBooking->id ?>"><span class="glyphicon glyphicon-random"> </span> Related Trip & Payment Detail</a>
					</h4>
		</div>
		<div id="related-booking-<?= $modelBooking->id ?>" class="panel-collapse collapse material-accordion__collapse">
			<div class="panel-body">
					<div class="row">
					<div class="col-md-12">
						<table class="table table-stripped">
						<caption>Related Trip</caption>
						<thead>
							<tr class="warning">
								<th width="10px">No</th>
								<th width="50px">Code</th>
								<th width="125px">Company</th>
								<th width="125px">Trip</th>
								<th width="50px">Pax*</th>
								<th width="125px">Date Of Trip</th>
								<th width="125px">Amount</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach($modelBooking->idPayment->tBookings as $x => $valBookings): ?>
							<?php if($valBookings->id != $modelBooking->id): ?>
							<tr>
							 <td><?= $x+1 ?></td>
							 <td width="50"><?= $valBookings->id ?></td>
							 <td><?= $valBookings->idTrip->idBoat->idCompany->name ?></td>
							 <td><?= $valBookings->idTrip->idRoute->departureHarbor->name." -> ".$valBookings->idTrip->idRoute->arrivalHarbor->name ?></td>
							 <td><?= count($valBookings->affectedPassengers) ?> Pax</td>
							 <td><?= date('d-m-Y',strtotime($valBookings->idTrip->date)) ?></td>
							 <td>Rp <?= number_format($valBookings->total_idr,0) ?></td>
							</tr>
						<?php else: ?>
							<tr class="info">
							 <td><?= $x+1 ?></td>
							 <td width="50"><?= $valBookings->id ?></td>
							 <td><?= $valBookings->idTrip->idBoat->idCompany->name ?></td>
							 <td><?= $valBookings->idTrip->idRoute->departureHarbor->name." -> ".$valBookings->idTrip->idRoute->arrivalHarbor->name ?></td>
							 <td><?= count($valBookings->affectedPassengers) ?> Pax</td>
							 <td><?= date('d-m-Y',strtotime($valBookings->idTrip->date)) ?></td>
							 <td>Rp <?= number_format($valBookings->total_idr,0) ?></td>
							</tr>
						<?php endif; ?>
						<?php endforeach; ?>
							<tr>
								<td colspan="3"></td>
								<td style="border-top: 2px solid #455A64;" colspan="2">Current Exchange <span class="font-tebal"> 1 <?= $modelBooking->idPayment->currency." =  Rp ".number_format($modelBooking->idPayment->exchange,0) ?></span></td>
								<td style="border-top: 2px solid #455A64;" class="font-tebal top-line"><?= $modelBooking->idPayment->idPaymentMethod->method ?></td>
								<td style="border-top: 2px solid #455A64;" class="font-tebal top-line">Rp <?= number_format($modelBooking->idPayment->total_payment_idr,0)."<br>".$modelBooking->idPayment->total_payment." ".$modelBooking->idPayment->currency  ?></td>
							</tr>
						</tbody>
						</table>
						<div class="alert material-alert material-alert_warning">Payment History</div>
						<!-- Payment History Start -->
						<!-- PAyapal Transaction Start -->
						<?php if($modelBooking->idPayment->id_payment_method == "1" && isset($modelBooking->idPayment->paypalTransaction)): ?>
						<table class="table table-stripped">
						 <caption>Payment Data From Paypal</caption>
							<thead>
							<tr class="danger">
								<th width="10px">ID</th>
								<th>Payer</th>
								<th width="10px">Amount</th>
								<th>Status</th>
								<th width="10px">Time</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?= $modelBooking->idPayment->paypalTransaction->id ?></td>
								<td><?= $modelBooking->idPayment->paypalTransaction->idPayer->full_name ?></td>
								<td><?= $modelBooking->idPayment->paypalTransaction->amount." ".$modelBooking->idPayment->paypalTransaction->currency ?></td>
								<td><?= $modelBooking->idPayment->paypalTransaction->idStatus->status ?></td>
								<td><?= date('d-m-Y H:i', strtotime($modelBooking->idPayment->paypalTransaction->datetime)) ?></td>
							</tr>
						</tbody>
						</table>

						<!-- PAyapal Transaction End -->
						<!-- WebHook Start -->
					<?php if(isset($modelBooking->idPayment->paypalTransaction->tWebhook)): ?>
						<table class="table table-stripped">
						 <caption>Notification From Paypal (WebHook)</caption>
							<thead>
							<tr class="success">
								<th width="10px">No</th>
								<th>Event</th>
								<th width="10px">Amount</th>
								<th>Status</th>
								<th width="10px">Time</th>
							</tr>
						</thead>
						<tbody>
						<?php
						$webHookData = $modelBooking->idPayment->paypalTransaction->tWebhook;
						 foreach($webHookData as $index => $valWebHook): 
						 	$number = $index+1;
						?>
							<tr>
								<td><?= $number ?></td>
								<td><?= $valWebHook->idEvent->event ?></td>
								<td><?= $valWebHook->amount." ".$valWebHook->currency ?></td>
								<td><?= $valWebHook->idStatus->status ?></td>
								<td><?= date('d-m-Y H:i', strtotime($valWebHook->datetime)) ?></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
						</tbody>
						</table>
						<!-- WebHook End -->
						<?php elseif($modelBooking->idPayment->id_payment_method == "2" && isset($modelBooking->idPayment->confirmPayment)): ?>
							<table class="table table-stripped">
						 	<caption>Payment data Bank Transfers (From Customer)</caption>
							<thead>
								<tr class="success">
									<th width="10px">Token</th>
									<th>Payer</th>
									<th width="10px">Amount</th>
									<th>Status</th>
									<th width="10px">Time</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?= $modelBooking->idPayment->token ?></td>
									<td><?= $modelBooking->idPayment->confirmPayment->name ?></td>
									<td>Rp <?= number_format($modelBooking->idPayment->confirmPayment->amount,0) ?></td>
									<td><?= $modelBooking->idPayment->statusPayment->status ?></td>
									<td><?= date('d-m-Y H:i', strtotime($modelBooking->idPayment->exp)) ?></td>
								</tr>
							</tbody>
							</table>

							<center><?= Html::img(['payment-slip','id'=>$modelBooking->id_payment], ['class' => 'img-responsive','onerror'=>'this.src="/error.png"']); ?></center>
						<?php endif; ?>
						<!-- Payment History End -->

						</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div> 
<i class="fa fa-exclamation"><b class="text-danger"> *Infant Not Included </b></i>
<style type="text/css">
	.custom-underline{
		border-bottom: 2px solid #455A64;
	}
	.font-tebal{
		font-weight: bold;
	}
</style>