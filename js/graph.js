graphid = 0;

function BarGraph(width, height, current, max, maxstr)
{
	this.width = width;
	this.height = height;
	this.perc = current / max;
   	if (this.perc > 1.0)
       	this.perc = 1.0;
   	this.barwidth = Math.round(width * this.perc);
	this.title = current + " of " + maxstr;
	this.color = "#ffcc66";
   	if (this.perc >= .90)
       	this.color = "#ff0000";
   	else if (this.perc >= .75)
       	this.color = "#ffff00";

    this.create = function() 
    {
   		var outs = "<div align='left' style='width: " + this.width +
			"px; border:1px outset #999999; height: " + this.height +
			"px; background-color: #cccccc; font-size:10px'>";
   		if (this.barwidth > 0)
   		{
       		outs += "<div style='width: " + this.barwidth + 
				"px; height: " + this.height + 
				"px; background-color: " + this.color +
				"; top: 0px; left: 0px;'><div align=center style='width:" + 
				this.width + "px'>" + 
				this.title + "</div></div>";
   		}
		else
		{
			outs += "<div align=center>" + this.title + "</div>";
		}

   		outs += "</div>";

		return outs;
	}
}

function Graph(type, style)
{
	// Graph type: vert | horiz
	this.type = type || "horiz";		

	// Style: 0 = % only, 1 = abs. and %, 2 = abs. only, 3 = none
    this.style = style || 0;						

	// Graph data: comma-sep values or array
	this.values; 						
	this.labels;
    this.labelBGColor = '#ffff99';
    this.legend;
    this.legendBGColor = '#F0F0F0';
    this.barWidth = 16;
    this.absValuesBGColor = '#ffffff';
    this.absValuesPrefix = '';

    this.barColors = new Array('#0000FF', '#FF0000', '#00E000', '#FF6600', '#663399', '#663300', '#FF0099', '#FFFF33');

    // CSS class names
    graphid++;

	//----------------------------------------------------------------------
	// Functions
	//----------------------------------------------------------------------
    this.set_style = function() 
	{
		var style = '<style> .cbar' + graphid + ' { ';
		style += 'border: 1px outset white;';
		style += '} .clabel' + graphid + ' { ';
		style += 'color: black;';
		style += 'background-color: ' + this.labelBGColor + '; ';
		style += 'border: 2px groove white;';
		style += 'font-family: Arial, Helvetica;';
		style += 'font-size: 10px; ';
		style += '} .clegend' + graphid + ' { ';
		style += 'color: black;'
		style += 'font-family: Arial, Helvetica;';
		style += 'font-size: 10px;';
		style += '} .clegendbg' + graphid + ' { ';
		style += 'background-color: ' + this.legendBGColor + '; ';
		style += 'border: 1px solid black';
		style += '} .cabsvalues' + graphid + ' { ';
		style += 'color: black;';
		style += 'background-color: ' + this.absValuesBGColor + '; ';
		style += 'border: 1px groove white;';
		style += 'font-family: Arial, Helvetica;';
		style += 'font-size: 10px;';
		style += '} .cpercvalues' + graphid + ' { ';
		style += 'color: black;';
		style += 'font-family: Arial, Helvetica;';
		style += 'font-size: 10px;';
		style += '} </style>';
		return style;
    }

    this.draw_bar = function(width, height, color) 
	{
		var bar = '<table border=0 cellspacing=0 cellpadding=0><tr>';
		bar += '<td class="cbar' + graphid + '" bgcolor=' + color + '>';
		bar += '<table border=0 cellspacing=0 cellpadding=0><tr>';
		bar += '<td width=' + width + ' height=' + height + '></td>';
		bar += '</tr></table>';
		bar += '</td></tr></table>';
		return bar;
	}

    this.show_value = function(val, align) 
	{
		val = Math.round(val);
		value = '<td class="cabsvalues' + graphid + '"';
		if (align) 
			value += ' align=' + align;
		value += ' nowrap>';
		value += '&nbsp;' + this.absValuesPrefix + val;
		value += '&nbsp;</td>';
		return value;
	}

    this.horiz_graph = function(lbl, val, sum, max) 
	{
		var perc = sum ? Math.round(max * 100 / sum) : 0;
		var mul = perc ? 100 / perc : 1;

		if (this.style < 2) 
			valSpace = 26;
      	else 
			valSpace = 12;

		var maxSize = Math.round(perc * mul + valSpace + 4);

		var graph = '';

		for (i = 0; i < val.length; i++) 
		{
           	label = (i < lbl.length) ? lbl[i] : '';
           	rowspan = val[i].length;
           	graph += '<tr><td class="clabel' + graphid + '"' + 
				((rowspan > 1) ? ' rowspan=' + rowspan : '') + 
				' align=center>';
           	graph += '&nbsp;' + label + '&nbsp;</td>';

           	for (j = 0; j < val[i].length; j++) 
			{
				percent = Math.round(sum ? val[i][j] * 100 / sum : 0);

           		if (this.style == 1 || this.style == 2)
            		graph += this.show_value(val[i][j], 'right');

              	graph += '<td height=100% width=' + maxSize + '>';
              	graph += '<table border=0 cellspacing=0 cellpadding=0 height=100%><tr>';

              	if (percent > 0) 
				{
                	graph += '<td>';
                	graph += this.draw_bar(Math.round(percent * mul), 
						this.barWidth, this.barColors[j]);
                	graph += '</td>';
              	}
              	else 
				{
					graph += '<td height=' + (this.barWidth + 4) + '></td>';
				}

              	graph += '<td class="cpercvalues' + graphid + '" width=' + 
					Math.round((perc - percent) * mul + valSpace) + ' nowrap>';
              	if (this.style < 2) 
					graph += '&nbsp;' + percent + '%';
              	graph += '&nbsp;</td>';
              	graph += '</tr></table></td></tr>';
              	if (j < val[i].length - 1) 
					graph += '<tr>';
           	}
        }

		return graph;
	}

    this.vert_graph = function(lbl, val, sum, max) 
	{
		var perc = sum ? Math.round(max * 100 / sum) : 0;
		var mul = perc ? 100 / perc : 1;

		if (this.style < 2) 
			valSpace = 14;
      	else 
			valSpace = 12;

		var graph = '<tr align=center valign=bottom>';
		for(i = 0; i < val.length; i++) 
		{
           	for(j = 0; j < val[i].length; j++) 
			{
           		percent = Math.round(sum ? val[i][j] * 100 / sum : 0);

           		graph += '<td>';
           		graph += '<table border=0 cellspacing=0 cellpadding=0 width=100%><tr align=center>';

           		graph += '<td class="cpercvalues' + graphid + 
					'" valign=bottom height=' + 
					Math.round((perc - percent) * mul + valSpace) + ' nowrap>';
              	if (this.style < 2) 
					graph += percent + '%';
              	graph += '</td>';
              	if (percent > 0) 
				{
                	graph += '</tr><tr align=center valign=bottom><td>';
                	graph += this.draw_bar(this.barWidth, 
						Math.round(percent * mul), this.barColors[j]);
               		graph += '</td>';
              	}
              	else 
				{
					graph += '</tr><tr><td width=' + (this.barWidth + 4) + 
						'></td>';
				}
              	graph += '</tr></table></td>';
            }
		}

        if (this.style == 1 || this.style == 2) 
		{
           	graph += '</tr><tr align=center>';
           	for (i = 0; i < val.length; i++) 
			{
           		for (j = 0; j < val[i].length; j++) 
				{
               		graph += this.show_value(val[i][j]);
           		}
            }
		}

		graph += '</tr><tr align=center>';
		for (i = 0; i < val.length; i++) 
		{
           	label = (i < lbl.length) ? lbl[i] : '';
           	colspan = val[i].length;
           	graph += '<td class="clabel' + graphid + 
				'"' + ((colspan > 1) ? ' colspan=' + colspan : '') + '>';
           	graph += '&nbsp;' + label + '&nbsp;</td>';
		}
		graph += '</tr>';

		return graph;
	}

    this.build_legend = function() 
	{
		var legend = '<table border=0 cellspacing=0 cellpadding=0><tr>';
		legend += '<td class="clegendbg' + graphid + '">';
		legend += '<table border=0 cellspacing=4 cellpadding=0>';
		var l = (typeof(this.legend) == 'string') ? 
			this.legend.split(',') : this.legend;

		for (i = 0; i < l.length; i++) 
		{
			legend += '<tr><td>' + 
				this.draw_bar(this.barWidth, this.barWidth, this.barColors[i]) +
				'</td>';
			legend += '<td class="clegend' + graphid + '" nowrap>' + 
				l[i] + '</td></tr>';
		}
		legend += '</table></td></tr></table>';
		return legend;
    }

    this.create = function() 
    {
		this.type = this.type.toLowerCase();
		var d = (typeof(this.values) == 'string') ? 
			this.values.split(',') : this.values;

		if (this.labels) 
		{
			var r = (typeof(this.labels) == 'string') ? 
				this.labels.split(',') : this.labels;
		}
      	else 
		{
			var r = new Array();
		}

		var label = graph = '';
		var percent = rowspan = colspan = 0;
		var val = new Array();
		var bars = (d.length > r.length) ? d.length : r.length;

      	graph += '<table border=0 cellspacing=0 cellpadding=0><tr>';
      	graph += '<td>';

      	if (this.legend)
        	graph += '<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td>';

      	var sum = max = 0;
      	val = new Array();

		for (var i = 0; i < bars; i++) 
		{
			if (typeof(d[i]) == 'string') 
			{
				drw = d[i].split(';');
			}
			else 
			{
          		drw = new Array();
          		drw[0] = d[i];
        	}

        	val[i] = new Array();

        	for (var j = v = 0; j < drw.length; j++) 
			{
          		val[i][j] = v = drw[j] ? parseFloat(drw[j]) : 0;
          		if (v > max) 
					max = v;
          		if (v > 0) 
					sum += v;
        	}
		}

       	graph += '<table border=0 cellspacing=2 cellpadding=0>';

       	if (this.type == 'horiz') 
		{
    		graph += this.horiz_graph(r, val, sum, max);
        }
        else
		{
    		graph += this.vert_graph(r, val, sum, max);
        }

        graph += '</table>';

		if (this.legend) 
		{
        	graph += '</td><td width=10>&nbsp;</td><td>';
        	graph += this.build_legend();
        	graph += '</td></tr></table>';
      	}

      	graph += '</td></tr></table>';
      	graph += this.set_style();

      	return graph;
	}
}
