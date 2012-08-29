<html>
<head>





</head>
<body>
<?php


		

	$stats =  'aseg.stats';  // gets stat file
	$data = file($stats);   // puts each line of data into its own array element

		
	for($i = count($data) - 1; $i >= 0; $i--) //following loop erases everly line that begins with a hashtag
  		{
  			if(substr($data[$i],0,1) == '#')
  				{
					unset($data[$i]);	
  				}
		}



		//construct an array that will hold the data once it is sorted into columns
	$dataTable = array(array(Index, SegId, NVoxels, Volume_mm3, StructName, normMean,
							normStdDev, normMin, normMax, normRange));
	

	
	//puts the data into csv format by replacing everyinstance of two spaces together with a single space, trimming excess
	//spaces from the beginning and end of each line, and then replacing all the spaces with commas.
	for($i = 68; $i < 68 +count($data); $i++)
		{
			$data[$i] = preg_replace('/\s+/', " ", $data[$i]);
			$data[$i] = trim($data[$i]);
			$data[$i] = preg_replace("/\s/", ",", $data[$i]);
		}

		
	//this seperates each of the rows into an array using the single white space between each string
	//note: $dataTable is in Row-Major order
	for($i = 68; $i < 68 +count($data); $i++)
		{
			$dataTable[$i - 67] = str_getcsv($data[$i],",");
		}
	


	// This finds the largest value in the normMean column which is used for making the bar graphs later
	$maxNormMean = 0;
	for($i = 1; $i < count($dataTable); $i++)
		{
			if($dataTable[$i][5] > $maxNormMean)
				{
					$maxNormMean = $dataTable[$i][5];
				}
		}
		
		
		
	/*
	 * $sortedTable will contain 3 smaller tables each containing data from a certain area of the brain
	 * $sorteTable[0] will contain the data for the left half of brain,
	 * $sorteTable[1] will contain the data for the right half of brain,
	 * and $sorteTable[2] will contain the data that doesn't fall specifically in either half,
	 */
	$sortedTable = array();
	$sortedTable[0][0] = $dataTable[0];
	$sortedTable[1][0] = $dataTable[0];
	$sortedTable[2][0] = $dataTable[0];
	for($i = 1; $i<count($dataTable); $i++)
		{
			$firstChar = substr($dataTable[$i][4],0,1);
				if($firstChar == "L")
					{
						$sortedTable[0][count($sortedTable[0])] = $dataTable[$i];
					}
				elseif($firstChar == "R")
					{
						$sortedTable[1][count($sortedTable[1])] = $dataTable[$i];
					}
				else
					{
						$sortedTable[2][count($sortedTable[2])] = $dataTable[$i];
					}
		}
		
		

	$jsSortedTable = json_encode($sortedTable);
	$jsMaxNormMean = json_encode($maxNormMean);

?>


<style>

	table	{	
				margin-left: 20 px;
				caption-side: top;
				border: 1px solid black;
				border-collapse: collapse;
			}
	

	td, th { 
				padding: 4px;
				border: 1px solid black;
			}
			
			
	caption	{
				font-style: italic;
				padding-top: 40 px;
				padding-bottom: 2px;
			}
			
	
	.bar 	{
				border-radius: 0px 3px 3px 0px;
				height:20px; 
				background-color:#444;
			}
	
	
	.bar2 	{
				border-radius: 0px 3px 3px 0px;
				height:20px; 
				background-color:#F22;
			}
			
	.barBackground {
				width:350px;
			}
			
			
</style>

 <script>


 
 	// these grab the data and maxVolume variable from the php. 
	sortedTable = <?= $jsSortedTable?>;
	maxNormMean = <?= $jsMaxNormMean?>;


	//this loop removes an reduntant left and Right strings from the data since it has already been sorted by hemisphere, 
	//replaces any instances of "_" and "-"  with a space, and makes sure that the first letter of every line is 
	//capatlized which makes the data a little neater 
	for(i = 0; i < 3; i++)
		{
			for(j = 1; j < sortedTable[i].length; j++)
				{
					for(k = 0; k < sortedTable[i][j].length; k++)
						{
							sortedTable[i][j][k] = sortedTable[i][j][k].replace(/-/g," ");
							sortedTable[i][j][k] = sortedTable[i][j][k].replace(/_/g," ");	
							sortedTable[i][j][k] = sortedTable[i][j][k].replace("Left ","");
							sortedTable[i][j][k] = sortedTable[i][j][k].replace("Right ","");
							sortedTable[i][j][k] = sortedTable[i][j][k].charAt(0).toUpperCase() + sortedTable[i][j][k].slice(1);
						}
				}	
		}


	function getPercentage(value)
	{
		percentage = value / maxNormMean;
		percentage = Math.round(percentage * 100);
		percentage = percentage + "%";
		return percentage;
	}	

	function getNormMeanPercentage(value,value2)
	{
		percentage = value / maxNormMean;
		percentage2 = value2 / maxNormMean;
		percentage2 = percentage2 / percentage
		percentage2 = Math.round(percentage2 * 100);
		percentage2 = percentage2 + "%";
		return percentage2;
	}	
	//Creates a table for the legend 
	document.write("<table style= 'margin-right:auto; margin-left:auto;'>");
	document.write("<caption style='padding-top: 15px;'> Legend </caption>");

	document.write("<tr>");
	document.write("<td> Normal Mean </td>");
	document.write("<td style= 'width: 30px; background-color:#444;'></td>");
	document.write("</tr>");
	document.write("<tr>");
	document.write("<td> Normal Standard Deviation </td>");
	document.write("<td style= 'width: 30px; background-color:#F22;'></td>");
	document.write("</tr>");
	document.write("</table>");
	

	//creates a table for the left hemisphere 
	document.write("<table>");
		document.write("<caption style='padding-top: 15px;'> Left Hemisphere </caption>");

			document.write("<tr>");
				document.write("<th style='width: 200px;'> Structure Name </th>");
				document.write("<th> Normal Mean</th>");	
				document.write("<th> Normal Standard Deviation</th>");
			document.write("</tr>");

	for(i = 1; i < sortedTable[0].length; i++)		
		{

			document.write("<tr>");
				document.write("<td>" + sortedTable[0][i][4] + "</td>");
				document.write("<td>" + sortedTable[0][i][5] + "</td>");
				document.write("<td>" + sortedTable[0][i][6] + "</td>");
				document.write("<td>");
				document.write("<div class='barBackground'>");
				document.write("</div>");
				document.write("<div class='bar' style='width: " + getPercentage(sortedTable[0][i][5]) +"';>");
				document.write("<div class='bar2' style='width: " + getNormMeanPercentage(sortedTable[0][i][5],sortedTable[0][i][6]) +"';>");
				document.write("</div>");
				document.write("</div>");
				document.write("</td>");
			document.write("</tr>");
		}			
	document.write("</table>");


	



	//Creates a table for the right Hemisphere 	
	document.write("<table>");
		document.write("<caption> Right Hemisphere </caption>");

			document.write("<tr>");
				document.write("<th style='width: 200px;'> Structure Name </th>");
				document.write("<th> Normal Mean</th>");	
				document.write("<th> Normal Standard Deviation</th>");	
			document.write("</tr>");

	for(i = 1; i < sortedTable[1].length; i++)		
		{
			document.write("<tr>");
				document.write("<td>" + sortedTable[1][i][4] + "</td>");
				document.write("<td>" + sortedTable[1][i][5] + "</td>");
				document.write("<td>" + sortedTable[1][i][6] + "</td>");
				document.write("<td>");
				document.write("<div class='barBackground'>");
				document.write("</div>");
				document.write("<div class='bar' style='width: " + getPercentage(sortedTable[1][i][5]) +"';>");
				document.write("<div class='bar2' style='width: " + getNormMeanPercentage(sortedTable[1][i][5],sortedTable[1][i][6]) +"';>");
				document.write("</div>");
				document.write("</div>");
				document.write("</td>");
			document.write("</tr>");
		}
	document.write("</table>");




	
	//Creates a table containg everything not in the right or left hemisphere 
	document.write("<table>");
		document.write("<caption> Everything else </caption>");
			document.write("<tr>");
				document.write("<th style='width: 200px;'> Structure Name </th>");
				document.write("<th> Normal Mean</th>");	
				document.write("<th> Normal Standard Deviation</th>");		
			document.write("</tr>");

	for(i = 1; i < sortedTable[2].length; i++)		
		{
			document.write("<tr>");
				document.write("<td>" + sortedTable[2][i][4] + "</td>");
				document.write("<td>" + sortedTable[2][i][5] + "</td>");
				document.write("<td>" + sortedTable[2][i][6] + "</td>");
				document.write("<td>");
				document.write("<div class='barBackground'>");
				document.write("<div class='bar' style='width: " + getPercentage(sortedTable[2][i][5]) +"';>");
				document.write("<div class='bar2' style='width: " + getNormMeanPercentage(sortedTable[2][i][5],sortedTable[2][i][6]) +"';>");
				document.write("</div>");
				document.write("</div>");
				document.write("</div>");
				document.write("</td>");
			document.write("</tr>");
		}
	document.write("</table>");




	
</script>
</body>

</html>