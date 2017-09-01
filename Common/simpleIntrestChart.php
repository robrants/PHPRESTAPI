<?php
include_once './myOracle.php';
 class simpleIntrestChart {

	 private $P; //Principle
	 private $i; //Interest
	 private $t; //Term
	 private $ltype;
	 private $pmnt; //monthly Payments
	 private $ipaid = 0; //Intrest Paid
	 private $Ppaid = 0; //Principle Paid
	 private $DB;
	 
	 public function __construct($P,$i,$t,$pmnt){
		 $this->DB = new myOracle();
		 $this->P = $P;
		 $this->i = $i;
		 $this->t = $t;
		 $this->ltype = $t.'y';
		 $this->pmnt = $pmnt;
	 }
	 private function calculate(){
		 $total = $this->P * (1+($this->t * $this->i)); //Total amount for payoff to term
		 $intrest = $total - $this->P;
		 $monthInterest = $intrest/(12*$this->t);
		 $this->ipaid += $monthInterest; //Interrest Paid to date
		 $this->Ppaid += ($this->pmnt - $monthInterest); //Principle paid to date
		 $this->P -= ($this->pmnt - $monthInterest); //Reduce Principle by Principle paid
	 }
	 
	 private function addPayment($x){
		 $insert = "insert into blackr.loanschdule values('$this->ltype',$x+1,$this->ipaid,$this->Ppaid,$this->P)";
		 return $this->DB->runInsert($insert);
	 }
	 	 
	 public function buildPaymentTable(){
		 $payments = ($this->t * 12);
		 echo "<table>
		 		<tr>
					<th>Payment number</th>
					<th>Principle</th>
					<th>Interest Paid</th>
					<th>Principle Paid</th>
				</tr>";
		 
		 for($x=0; $x < $payments; $x++){
			 if($x != 0 and $x%12 == 0){ //If the loan has matured by 12 months reduce the term for calculating interest
				 $this->t--;
			 }			 
			 $this->calculate();
			 echo "<tr>
			 			<td>$x</td>
						<td>$this->P</td>
						<td>$this->ipaid</td>
						<td>$this->Ppaid</td>
					</tr>";
			 //if($this->addPayment($x))continue;
			 //else break;
		 }
		 echo "</table>";
	 }
}
?>
