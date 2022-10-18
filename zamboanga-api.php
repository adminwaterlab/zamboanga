<?php
include('../init.php');
include('../functions.php');
$datenow =  date('Y-m-d');
$timenow =  date('H:i:s');

$datetime = $datenow." ".$timenow;

date_default_timezone_set('Asia/Manila');
if(isset($_GET['location'])){
	echo "zamboanga";
}

if(isset($_GET['readremindersid'])){
	$id = $_GET['readremindersid'];

	//Update Reminder status
	$update = mysqli_query($con,"UPDATE reminders SET message_status='read' WHERE id='$id'");
	$return_arr = array();

$fetch = mysqli_query($con,"SELECT * FROM reminders WHERE id='$id' ") or die(mysqli_error($con)); 

while ($row = mysqli_fetch_array($fetch)) {
    $row_array['message_created'] = date('Y/m/d',strtotime($row['message_created']));
    $row_array['message_from'] = $row['message_from'];
    $row_array['message_content'] = $row['message_content'];
    $row_array['id'] = $row['id'];
    $row_array['message_status'] = $row['message_status'];

    array_push($return_arr,$row_array);
}

echo json_encode($return_arr);

}

if(isset($_GET['reminders'])){
	$usercode = $_GET['usercode'];

	$return_arr = array();

$fetch = mysqli_query($con,"SELECT * FROM reminders WHERE message_to='$usercode' ORDER BY message_status DESC") or die(mysqli_error($con)); 

while ($row = mysqli_fetch_array($fetch)) {
    $row_array['message_created'] = date('Y/m/d',strtotime($row['message_created']));
    $row_array['message_from'] = $row['message_from'];
    $row_array['message_content'] = substr($row['message_content'], 0, 15) . '...';
    $row_array['id'] = $row['id'];
    $row_array['message_status'] = $row['message_status'];
    $row_array['link'] = "reminders-view.html?readremindersid=".$row['id'];

    array_push($return_arr,$row_array);
}

echo json_encode($return_arr);

}

if(isset($_GET['reminders_count'])){
	$usercode = $_GET['usercode'];
	$sql = mysqli_query($con,"SELECT id FROM reminders WHERE message_to='$usercode' AND message_status='unread'") or die(mysqli_query($con));
	$count = mysqli_num_rows($sql);

	//echo $count;
	if($count!=0){
		echo "<script>alert('You have ".$count." unread reminder/s!');</script>";
		echo $count;
	}else{
		echo $count;
	}

}

if(isset($_GET['daily_client'])){
	$usercode = $_GET['usercode'];
	$userid = $_GET['userid'];

	$return_arr = array();

$fetch = mysqli_query($con,"SELECT date_sampling,COUNT(DISTINCT acct_no) as tclient,COUNT(id) as tsample FROM `result` WHERE MONTH(date_sampling)=MONTH(now())
       AND YEAR(date_sampling)=YEAR(now()) AND code='$usercode' GROUP BY date_sampling"); 

while ($row = mysqli_fetch_array($fetch)) {
    $row_array['date_sampling'] = date('Y/m/d D',strtotime($row['date_sampling']));
    $row_array['tclient'] = $row['tclient'];
    $row_array['tsample'] = $row['tsample'];

    array_push($return_arr,$row_array);
}

echo json_encode($return_arr);

}

if(isset($_GET['date_delivered'])){
	$userid = $_GET['userid'];
	$usercode = $_GET['usercode'];
	$date = $_GET['date_delivered'];
	$id = $_GET['resultid'];

	$sql = mysqli_query($con,"UPDATE result SET delivered_by_id='$userid',delivered_by='$code',delivered_date='$date' WHERE id='$id' ");

	if($sql){
		echo "<script>window.location.href = 'fordtr.html';</script>";
	}else{
		echo "Failed. Please try again.";
	}
}

if(isset($_GET['expensesid'])){
	$id = $_GET['expensesid'];

	$sql = mysqli_query($con,"DELETE FROM expense WHERE id='$id'") or die(mysqli_error($con));
	if($sql){
		echo "<script>window.location.href = 'expenses.html';</script>"; 
	}else{
		echo "<script>window.location.href = 'expenses.html';</script>";
	}
}

if(isset($_GET['get_expenses'])){
		$usercode = $_GET['userid'];
		$dategroup = $_GET['dategroup'];

	$sql = mysqli_query($con,"SELECT * FROM expense WHERE voucher_date=CURDATE() AND user_id='$usercode' ORDER BY ID DESC") or die(mysqli_error($con));

	?>
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th></th>
				<th>Date</th>
				<th>Establishment</th>
				<th>Particulars</th>
				<th>Amount</th>
			</tr>
		</thead>
		<tbody style="font-size:10px !important;">
			<?php
			$total = 0;
			while ($row=mysqli_fetch_array($sql)) {
				?>
				<tr>
					<td><a href="deleteexpenses.html?expensesid=<?php echo $row['id'];?>" class="btn btn-xs btn-danger">X</a></td>
					<td><?php echo date('M d, Y',strtotime($row['voucher_date']));?></td>
					<td><?php echo $row['establishment'];?></td>
					<td><?php echo $row['particulars'];?></td>
					<td><?php echo $row['gross'];?></td>
				</tr>
				<?php
				$price= $row['gross'];
  				$total += $price;
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">Total</td>
				<td></td>
				<td></td>
				<td><?php echo number_format($total,2);?></td>
			</tr>
		</tfoot>
	</table>
	<?php
}

if(isset($_GET['fordtr']))
{
	$usercode = $_GET['usercode'];

	$sql = mysqli_query($con,"SELECT trout_to,date_sampling,id,lab_no,company,client,balance FROM result WHERE trout_to='$usercode' AND delivered_date='0000-00-00' AND YEAR(date_sampling)=YEAR(CURDATE())") or die(mysqli_error($con));
	?>
			<?php
			while ($row=mysqli_fetch_array($sql)) {
				?>
				<div class="panel panel-default">
				  <div class="panel-body">
				  	<p><?php echo date('M d, Y',strtotime($row['date_sampling']));?></p>
				    <p><strong><?php echo $row['company']."/".$row['client'];?></strong></p>
				    <?php
				    if($row['balance']==0.00){
				    	?>
				    	<span class="label label-success">PAID</span>
				    	<?php
				    }else{
				    	?>
				    	<span class="label label-danger"><?php echo $row['balance'];?></span>
				    	<?php
				    }
				    ?>
				    <span class="label label-info"><?php echo $row['lab_no'];?></span>
				    <a href="update-dtr.html?resultid=<?php echo $row['id'];?>" class="label label-warning">Update</a>
				  </div>
				</div>
				<?php
			}
			?>
	<?php
}
//Add Maps Location
if(isset($_GET['location'])){
	$acct_no = $_GET['account_no'];
	$location = $_GET['location'];
	$userid = $_GET['userid'];
	$usercode = $_GET['usercode'];

	$sql = mysqli_query($con,"UPDATE client SET maps='$location',mapsaddbyid='$userid',mapsaddbycode='$usercode' WHERE id='$acct_no'");

	if($sql){
		echo "<script>window.location.href = 'account.html?customer_id=".$acct_no."&message=success';</script>";
	}else{
		echo "<script>window.location.href = 'account.html?customer_id=".$acct_no."&message=failed';</script>";
	}
}
//Add Expenses
if(isset($_GET['addexpenses']))
{
	$usercode = $_GET['usercode'];
	$userid = $_GET['userid'];
	$voucher_date = $_GET['date_receipt'];
	$name = $_GET['fname'];
	$particulars = $_GET['particulars'];
	$establishment = $_GET['establishment'];
	$remarks = $_GET['remarks'];
	$gross = $_GET['amount'];
	$net_vat = $amount / 1.12;
	$vat = $net_vat * 0.12;
	$remarks = $_GET['remarks'];
	$fullname = $_GET['fname']." ".$_GET['lname'];

	$sql = mysqli_query($con,"INSERT INTO expense (`particulars`,`added_by`,`establishment`,`name`,`voucher_date`,`gross`,`net_vat`,`vat`,`remarks`,`user_id`) VALUES('$particulars','$fullname','$establishment','$name','$voucher_date','$gross','$net_vat','$vat','$remarks','$userid')") or die(mysqli_error($con));
	if($sql){
		echo "<script>window.location.href = 'expenses.html';</script>";
	}else{
		echo "Please try Again";
	}
}
//my Expenses
if(isset($_GET['myexpenses'])){
	$usercode = $_GET['userid'];
	$dategroup = $_GET['dategroup'];

	if($dategroup=='weekly'){
		$sql = mysqli_query($com,"SELECT * FROM expenses WHERE YEARWEEK(voucher_date) = YEARWEEK(NOW()) AND user_id='$usercode';") or die(mysqli_error($con));
	}elseif($dategroup=='monthly'){
		$sql = mysqli_query($com,"SELECT * FROM expenses WHERE MONTH(voucher_date) = MONTH(CURDATE()) AND user_id='$usercode';") or die(mysqli_error($con));
	}elseif($dategroup=='daily'){
		$sql = mysqli_query($con,"SELECT * FROM expense WHERE voucher_date=CURDATE() AND user_id='$usercode' ORDER BY ID DESC") or die(mysqli_error($con));
	}

	?>
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>Date</th>
				<th>Establishment</th>
				<th>Particulars</th>
				<th>Amount</th>
			</tr>
		</thead>
		<tbody style="font-size:10px !important;pointer-events: none;">
			<?php
			$total = 0;
			while ($row=mysqli_fetch_array($sql)) {
				?>
				<tr>
					<td><?php echo date('M d, Y',strtotime($row['voucher_date']));?></td>
					<td><?php echo $row['establishment'];?></td>
					<td><?php echo $row['particulars'];?></td>
					<td><?php echo $row['gross'];?></td>
				</tr>
				<?php
				$price= $row['gross'];
  				$total += $price;
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td>Total</td>
				<td></td>
				<td></td>
				<td><?php echo number_format($total,2);?></td>
			</tr>
		</tfoot>
	</table>
	<?php
}
//my crf
if(isset($_GET['mycrf']))
{
	$usercode = $_GET['usercode'];

	$sql = mysqli_query($con,"SELECT type,acct_no,status,date_sampling,id,lab_no,company,client FROM result WHERE date(date_sampling)=CURDATE() AND code='$usercode' ORDER BY ID DESC") or die(mysqli_error($con));
	?>
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>No</th>
				<th>ACCT NO.<br>ID NO.</th>
				<th>DoS</th>
				<th>CRF</th>
				<th>Type</th>
				<th>Client</th>
				<th>Opt</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 1;
			while ($row=mysqli_fetch_array($sql)) {
				?>
				<tr id="<?php echo $row['id'];?>">
					<td><?php echo $i;?></td>
					<td><?php echo $row['id'];?></td>
					<td><?php echo date('M d, Y',strtotime($row['date_sampling']));?></td>
					<td><?php echo $row['lab_no'];?></td>
					<td><?php echo result_type($row['type']);?></td>
					<td><?php echo $row['client']."/".$row['company'];?></td>
					<?php
						if($row['status']=='pending_a'){
								?>
									<td><a href="edit-crf.html?id=<?php echo $row['id'];?>" class="btn btn-xs btn-success"><i class="fas fa-edit"></i></a> <a href="delete-crf.html?id=<?php echo $row['id'];?>" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a></td>
								<?php
						}else{
							echo "<td>All ready Approved</td>";
						}
					?>
				</tr>
				<?php
				$i++;
			}
			?>
		</tbody>
	</table>
	<?php
}
//my dtr
if(isset($_GET['mydtr']))
{
	$usercode = $_GET['usercode'];

	$sql = mysqli_query($con,"SELECT id,lab_no,company,client FROM result WHERE date(delivered_date)=CURDATE() AND delivered_by='$usercode' ORDER BY ID DESC") or die(mysqli_error($con));

	$count = mysqli_num_rows($sql);

	if($count==0){
		echo "No Data found.";
	}else{
		?>
			<table class="table table-bordered table-condensed">
				<thead>
					<tr>
						<th>CRF</th>
						<th>Client</th>
					</tr>
				</thead>
				<tbody style="font-size:10px !important;pointer-events: none;">
					<?php
					$i=1;
					while ($row=mysqli_fetch_array($sql)) {
						?>
						<tr id="<?php echo $row['id'];?>">
							<td><?php echo $i;?></td>
							<td><?php echo $row['lab_no'];?></td>
							<td><?php echo $row['client']."/".$row['company'];?></td>
						</tr>
						<?php
						$i++;
					}
					?>
				</tbody>
			</table>
			<?php
	}
}
//my collection
if(isset($_GET['mycollection'])){

	$usercode = $_GET['usercode'];
	$userid = $_GET['userid'];

	$sql = mysqli_query($con,"SELECT paymentFor,sales_id,payer,receipt_type,receipt,amount,reference FROM sales WHERE date(created_at)=CURDATE() AND added_by='$userid' ORDER BY sales_id DESC") or die(mysqli_error($con));
	?>
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>CRF No.</th>
				<th>Client</th>
				<th>Receipt</th>
				<th>Amount</th>
				<th></th>
			</tr>
		</thead>
		<tbody style="font-size:10px !important;">
			<?php
			$total = 0;
			while ($row=mysqli_fetch_array($sql)) {
				?>
				<tr id="<?php echo $row['sales_id'];?>">
					<td><?php echo $row['paymentFor'];?></td>
					<td><?php echo clientname($row['payer']);?></td>
					<td><?php echo strtoupper($row['receipt_type'])." ".$row['receipt'];?></td>
					<td><?php echo $row['amount'];?></td>
					<td><a href="delete-payment.html?sales_id=<?php echo $row['sales_id'];?>&id_no=<?php echo $row['reference'];?>&amount=<?php echo $row['amount'];?>" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></a></td>
				</tr>
				<?php
				$price= $row['amount'];
  				$total += $price;
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td>Total</td>
				<td></td>
				<td></td>
				<td><?php echo number_format($total,2);?></td>
			</tr>
		</tfoot>
	</table>
	<?php
}
if(isset($_GET['get_account_info'])){
	$account_no = $_GET['account_no'];
	$sql = mysqli_query($con,"SELECT * FROM client WHERE id='$account_no'");
	$row = mysqli_fetch_array($sql);
	?>
	<p><a href="editclient.html?id=<?php echo $row['id'];?>">Edit</a></p>
	<p>
		<strong><?php echo $row['company'];?></strong>
	</p>
	<p>
		<strong><?php echo $row['owner'];?></strong>
	</p>
	<p>
		<?php echo $row['complete_add'];?>
	</p>
	<p>
		<?php echo $row['contact_no'];?>
	</p>
	<p>
		<?php echo $row['email'];?>
	</p>
	<p>
		<strong>Landmark</strong> : <?php echo $row['landmark'];?>
	</p>
	<p>
		<strong>Note</strong> : <?php echo $row['notes'];?>
	</p>
	<p>
		<a href="alltest.html?customer_id=<?php echo $account_no;?>" class="btn btn-primary btn-block">View All Test</a>
		<a href="addtest.html?customer_id=<?php echo $account_no;?>" class="btn btn-warning btn-block">ADD TEST</a>
		<?php 
		/*if($row['maps']!=""){
			?>
			<a href="" target="_blank" class="btn btn-success btn-block">View MAPS</a>
			<?php
		}else{
			?>
			<a href="addmaps.html?customer_id=<?php echo $account_no;?>" target="_blank" class="btn btn-success btn-block">Add MAPS</a>
			<?php
		}*/
		?>
	</p>
	<div class="table-responsive">
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>DoS</th>
				<th>CRF</th>
				<th>Type</th>
				<th>Balance</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql = mysqli_query($con,"SELECT lab_no,acct_no,date_sampling,id,type,amount,balance,contact_no FROM result WHERE acct_no='$account_no' AND balance<>'0.00'") or die(mysqli_error()); 
				while ($row = mysqli_fetch_array($sql)) {
					?>
						<tr>
							<td><?php echo $row['date_sampling'];?></td>
							<td><?php echo $row['lab_no'];?></td>
							<td><?php echo result_type($row['type']);?></td>
							<td><?php echo $row['balance'];?></td>
							<td><a href="payment.html?result_id=<?php echo $row['id'];?>&client_id=<?php echo $row['acct_no'];?>&receipt-number=<?php echo $row['contact_no'];?>" class="btn btn-xs btn-success">+</a></td>
						</tr>
					<?php
				}
			?>
		</tbody>
	</table>
	</div>
	<?php
}
if(isset($_GET['get_account_info2'])){
	$account_no = $_GET['account_no'];
	$sql = mysqli_query($con,"SELECT * FROM client WHERE id='$account_no'");
	$row = mysqli_fetch_array($sql);
	?>
	<p>
		<strong><?php echo $row['company'];?></strong>
	</p>
	<p>
		<strong><?php echo $row['owner'];?></strong>
	</p>
	<p>
		<?php echo $row['complete_add'];?>
	</p>
	<p>
		<?php echo $row['contact_no'];?>
	</p>
	<p>
		<?php echo $row['email'];?>
	</p>
	<?php
}
if(isset($_GET['get_account_alltest'])){
	$account_no = $_GET['account_no'];
	$sql = mysqli_query($con,"SELECT * FROM client WHERE id='$account_no'");
	$row = mysqli_fetch_array($sql);
	?>
	<p>
		<strong><?php echo $row['company'];?></strong>
	</p>
	<p>
		<strong><?php echo $row['owner'];?></strong>
	</p>
	<p>
		<?php echo $row['complete_add'];?>
	</p>
	<p>
		<?php echo $row['contact_no'];?>
	</p>
	<p>
		<?php echo $row['email'];?>
	</p>
	<p>
		<a href="addtest.html?customer_id=<?php echo $account_no;?>" class="btn btn-warning btn-block">ADD TEST</a
	</p>
	<div class="table-responsive">
	<table class="table table-bordered table-condensed">
		<thead>
			<tr>
				<th>DoS</th>
				<th>CRF</th>
				<th>Type</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql = mysqli_query($con,"SELECT lab_no,acct_no,date_sampling,id,type,amount FROM result WHERE acct_no='$account_no' ORDER BY id DESC") or die(mysqli_error()); 
				while ($row = mysqli_fetch_array($sql)) {
					?>
						<tr>
							<td><?php echo $row['date_sampling'];?></td>
							<td><?php echo $row['lab_no'];?></td>
							<td><?php echo result_type($row['type']);?></td>
						</tr>
					<?php
				}
			?>
		</tbody>
	</table>
	</div>
	<?php
}
?>