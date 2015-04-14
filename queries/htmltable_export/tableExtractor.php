<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>HTML table Extract & Export Any Format</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
		<meta name="description" content="Extract HTML Table from any website and export different formats like JSON, XML, PDF, PNG, XLS, Word, Powerpoint">
		<meta name="keywords" content="Extract HTML Table, Export Html Table, table2json,table2csv,table2xml,table2pdf,table2png,table2word,table2powerpoint,table2sql, table jquery plugin, ngiriraj" />
		<meta name="google-site-verification" content="v-yNd2u5KPjFM1uQk2L2ntXc_5O4HXTqkBSDDZ85-4M" />
		

		<?php
		include_once ("../common_header.php");
		?>
		
		<?php
			include('simple_html_dom.php');
			$data_url = (@$_GET['url']) ?: "http://www.metrotraintimings.com/Chennai/SuburbanTrainCodes.htm";
			#echo $data_url;
			
			function curl_read($data_url){
				$ch = curl_init ($data_url);
				curl_setopt($ch, CURLINFO_HEADER_OUT, true); 
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_VERBOSE, true);
				$data=curl_exec($ch);
				curl_close ($ch);
				return  $data;
			}


			#print curl_read($data_url);
			$html = str_get_html(curl_read($data_url));
		?>
	
	<script type="text/javascript" src="tableExport.js"></script>
	<script type="text/javascript" src="jquery.base64.js"></script>
	<script type="text/javascript" src="html2canvas.js"></script>
	<script type="text/javascript" src="jspdf/libs/sprintf.js"></script>
	<script type="text/javascript" src="jspdf/jspdf.js"></script>
	<script type="text/javascript" src="jspdf/libs/base64.js"></script>
	
		<script type="text/javaScript">
	
		$(document).ready(function(){
			
		
			

		
		});
	</script>
	
	
    <body class="skin-black">
	<?php
		include_once ("../page_header.php");
	?>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
	<?php
		include_once ("../left_side_menu.php");
	?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">                
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        HTML table Extract & Export Any Format
                        <small>jquery Plugin</small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="/"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li>My jQuery Plugin</li>
                        <li class="active">tableExtractor</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
						
                        <div class="col-md-12">
						
                            <div class="box">
					
                                <div class="box-body table-responsive" id='ptable'>
									  <div class="form-group">
										<form class="navbar-form" method="GET" action="">
										<label for="exampleInputEmail1">Email address</label>
										<input value ="<?php echo $data_url;?>" id='url' name='url' type="text" placeholder="Enter url"  class="form-control">
										<button class="btn btn-primary" type="submit">Submit</button>
										</form>
										</div>

										
										<table width=100% >
										<?php
										// Find all tables
										$i=0;
										foreach($html->find('table') as $e) {
											$i++;				
											?>
												<tr class='btn-info'><td>Export Below Table Data (#<?php echo $i; ?>) as 
												<a onClick ="$('#export_<?php echo $i; ?>').tableExport({type:'xml'});"><img src='icons/xml.png' width='24px'></a>
												<a onClick ="$('#export_<?php echo $i; ?>').tableExport({type:'json',escape:'false'});"><img src='icons/json.png' width='24px'></a>
												<a onClick ="$('#export_<?php echo $i; ?>').tableExport({type:'excel',escape:'true'});"><img src='icons/xls.png' width='24px'></a>
												<a onClick ="$('#export_<?php echo $i; ?>').tableExport({type:'csv'});"><img src='icons/csv.png' width='24px'></a>
												<a onClick ="$('#export_<?php echo $i; ?>').tableExport({type:'png'});"><img src='icons/png.png' width='24px'></a>
												</td></tr>
										<?php
											print "<tr><TD><table id='export_".$i."' border=1>".$e->innertext. "</table></td></tr>";	
										}
								
										if ($i == 0){
											echo "oops! No Tables in <a href='".$data_url."' target='_blank'>".$data_url."</a>, Please check it";
										}else{
											echo "We have found $i tables from this url (<a href='".$data_url."' target='_blank'>".$data_url."</a>), Please verify it";
										}
										?>
										</table>	
										
										<h3>Sample URLs</h3>
										<ul>
											<li><a href="?url=http://simple.wikipedia.org/wiki/List_of_countries_by_population">http://simple.wikipedia.org/wiki/List_of_countries_by_population</a></li>
											<li><a href="?url=http://india.gov.in/my-government/whos-who/governors">http://india.gov.in/my-government/whos-who/governors</a></li>
											<li><a href="?url=http://india.gov.in/my-government/whos-who/chief-ministers">http://india.gov.in/my-government/whos-who/chief-ministers</a></li>
											
										</ul>
								</div>
							
                               </div><!-- /.box-body -->
                               
                        </div><!-- /.box -->
                        
                        </div><!-- /.col -->
                        
                    </div>
                </section><!-- /.content -->                
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->
    </body>
	
	<?php
		include_once ("../common_footer.php");
	?>
</html>