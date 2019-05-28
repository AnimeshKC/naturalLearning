
var words,
    connections,
    minimumDist,
    maximumDist,
    primaryWord,
    nodes;


function init() {

    // get incoming data from the server
     
    words           = incoming.words,
    connections          = incoming.connections,
    minimumDist        = incoming.minimumDist,
    maximumDist     = incoming.maximumDist,
    contentArray = incoming.contentArray,
    primaryWord     = incoming.primaryWord;
    
    wordArray = [];
    console.log(200);
    console.log('Content');
    console.log(contentArray);
    
for (var i = 0; i < words.length; i++) {
    wordArray.push({
        frequency: words[i][0],
        name: words[i][1],
        paragraphs: contentArray[i][1],
        tables: contentArray[i][2]
    });
}
    linkArray = [];
    maxWeight = 0;
    minWeight = 100000000;
for (var i = 0; i < connections.length; i++){
    if (connections[i][2] > maxWeight){
        maxWeight = connections[i][2];
    }
    else if (connections[i][2] < minWeight){
        minWeight = connections[i][2];
    }
    linkArray.push({
        source: connections[i][0],
        target: connections[i][1],
        weight: connections[i][2]
    })

}
    
 console.log(maxWeight);
 console.log(minWeight);
    
    function scaleWeight(minWeight, maxWeight, currentWeight){
        if (currentWeight == maxWeight){
            return 0.1
        }
        else if (currentWeight == minWeight){
            return 1
        }
        else{
            return (1- ((minWeight-maxWeight)*(0.9))/(minWeight-maxWeight))
        }
    }
    
    //console.log(scaleWeight(minWeight, maxWeight, 40));
    // Define the div for the tooltip
    var graph = {
        nodes: wordArray,
        links: linkArray
    };
    var canvas = d3.select("#network")
    width = canvas.attr("width"),
    height = canvas.attr("height");
    //console.log(primaryWord);
    console.log(graph);
    console.log(connections);
    /*var k = Math.sqrt(words.length / (width * height));
    console.log(width);
    console.log(words.length);
    console.log(k);*/
        //test radius
        var r = 3,
        ctx = canvas.node().getContext("2d"),
        simulation = d3.forceSimulation()
        .force("x", d3.forceX(width/2))
        .force("y", d3.forceY(height/2))
        .force("collide", d3.forceCollide(r))
        .force("charge", d3.forceManyBody()
            .strength(-50))
        
        .force("link", d3.forceLink()
      .id(function (d) { return d.name; })
     .distance(function (d){return 3* (maxWeight - d.weight + 10)})
     .strength (function (d) {return scaleWeight(minWeight, maxWeight, d.weight)})
              )
        .on("tick", update);
    
    var tooltip = d3.select("body")
    .append("div")
    .attr("width",50)
    .attr("height",100)
    .attr("data-html", "true")
    .style("position", "absolute")
    .style("overflow-y", "scroll")
    .style("z-index", "10")
    .style("visibility", "hidden")
    .html("");
    canvas
    .on("mousemove", mousemove)
    .on("mouseout",mouseout);
    canvas
      .call(d3.drag()
          .container(canvas.node())
          .subject(dragsubject)
          .on("start", dragstarted)
          .on("drag", dragged)
          .on("end", dragended));
    simulation.nodes(graph.nodes);
     simulation.force("link")
    .links(graph.links);
    
    
    
    function findDist(centerWord, connections, currentWord){
        for (i = 0; i < connections.length; i++){
            if (connections[i][0] == centerWord && connections[i][1] == currentWord){
                return connections[i][2];
            }
            else if (connections[i][1] == centerWord && connections[i][0] == currentWord){
                return connections[i][2]*-1;
            }
            else if (centerWord == currentWord){
                return 0;
            }
        }
    }
    
    
    /*graph.nodes.forEach(function(d){
                        d.dist = findDist(primaryWord, connections, d.name);
                        d.x = height/2 - d.dist;
                        d.y = width/2 - d.dist;
                        });*/

     function update(){
        ctx.clearRect(0, 0, width, height);
        ctx.beginPath();
        graph.nodes.forEach(drawNode);
        //graph.nodes.forEach(resetNode);
        ctx.fill();
        
        ctx.beginPath();
        //graph.links.forEach(drawLink);
        ctx.stroke();
        
    }
    
    function resetNode(d){
        d.fx = null;
        d.fy = null;
    }
    function dragsubject() {
    return simulation.find(d3.event.x, d3.event.y);
  }
    
    function tick() {
  // Update positions of circle elements.
  node.attr("cx", function(d) { return d.x; })
      .attr("cy", function(d) { return d.y; });
}
    
    update();
    
    function dragstarted() {
  //if (!d3.event.active) simulation.alphaTarget(0.3).restart();
  simulation.restart();
  graph.nodes.forEach(resetNode);
  simulation.alphaTarget(0.3);
  d3.event.subject.fx = d3.event.subject.x;
  d3.event.subject.fy = d3.event.subject.y;
  console.log(d3.event.subject);
}
function dragged() {
  d3.event.subject.fx = d3.event.x;
  d3.event.subject.fy = d3.event.y;
}
function dragended() {
  if (!d3.event.active) simulation.alphaTarget(0);
    //d3.event.subject.fx = null
    //d3.event.subject.fy = null
  simulation.force("x", d3.forceX(d3.event.subject.x))
        .force("y", d3.forceY(d3.event.subject.y));

    //d3.forceRadial(r, d3.event.x, d3.event.y);
    //d3.forceCenter([d3.event.x,d3.event.y]);
    d3.event.subject.fx = d3.event.x;
    d3.event.subject.fy = d3.event.y;
}
function mousemove() {
    var point = d3.mouse(this);
    var nodePoint; 
    var minDistance = Infinity;
    var nodeName,
        nodeX,
        nodeY,
        nodeLength,
        nodeParagraphLength;
    var state = 0;
    
    graph.nodes.forEach(function(d){
        var distX = d.x - point[0];
        var distY = d.y - point[1];
        var distance = Math.sqrt((distX * distX) + (distY * distY));
        if (distance < minDistance && distance < r +10) {
			// drawCircles(d);
			minDistance = distance;
			nodePoint = d;
            nodeX = d.x;
            nodeY = d.y;
            nodeName = d.name;
            nodeParagraph = d.pararaphs; 
            nodeParagraphLength = d.paragraphs.length;
            nodeParagraph = "";
            for (var i = 0; i < nodeParagraphLength; i++){nodeParagraph += d.paragraphs[i] + "<br>"};
            nodeTableLength = d.tables.length;
            nodeTable = ""
            for (var i = 0; i <nodeTableLength; i++){nodeTable += d.tables[i] + "<br>"};
    }
    })
    
         tooltip
        .attr("x", nodeX)
        .attr("y", nodeY)
    //console.log("NodeName is: " + nodeName);
    tooltip.style("top",nodeY + "px").style("left",nodeX + "px")
    
    
    var inputString = "";
    if (nodeName){
        inputString += "Key Word:   " + nodeName + "<br />  # of Paragraphs: " + nodeParagraphLength + "<br /> # of Tables: " + nodeTableLength;
        if (nodeParagraphLength > 0){
            inputString += "<br />  Paragraphs: <br />" + nodeParagraph;
        }
        if (nodeTableLength >0){
            inputString += "<br />Tables: " + "<br />" + nodeTable;
        }
        tooltip.html(inputString
                    )
        //tooltip.html(inputString);
    }
    else{
        return tooltip.style("visibility", "hidden")
    }
    
    return tooltip.style("visibility", "visible")
}
function mouseout() {
    return tooltip.style("visibility", "hidden")
    
}
    
    function drawNode(d){
        ctx.moveTo(d.x, d.y);
        ctx.arc(d.x, d.y, r, 0, 2*Math.PI);
    }
    function drawLink(l) {
  ctx.moveTo(l.source.x, l.source.y);
  ctx.lineTo(l.target.x, l.target.y);
}
}
init();
//window.addEventListener("load", init);
