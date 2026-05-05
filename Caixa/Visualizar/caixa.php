<?php
include("conexao.php");

$produtos = mysqli_query($conexao, "SELECT * FROM produtos");

$custo_total = mysqli_fetch_assoc(
mysqli_query($conexao,"SELECT SUM(preco_custo*estoque_atual) as total FROM produtos")
)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Caixa</title>
<link rel="stylesheet" href="caixa.css">
</head>
<body>

<div class="wrap">
<h1>Caixa</h1>

<div id="tela1" class="grid">

<div class="card">
<h2>Produtos</h2>

<div class="table">
<div class="thead">
<div>Código</div><div>Produto</div><div>Preço</div><div>Estoque</div><div></div>
</div>

<div class="tbody">
<?php while($p = mysqli_fetch_assoc($produtos)) { ?>
<div class="tr">
<div><?php echo $p['id']; ?></div>
<div><?php echo $p['nome']; ?></div>
<div><?php echo number_format($p['preco_venda'],2,',','.'); ?></div>
<div id="est_<?php echo $p['id']; ?>"><?php echo $p['estoque_atual']; ?></div>
<div>
<button onclick="addCarrinho('<?php echo $p['id']; ?>','<?php echo $p['nome']; ?>',<?php echo $p['preco_venda']; ?>,<?php echo $p['preco_custo']; ?>)">+</button>
</div>
</div>
<?php } ?>
</div>
</div>
</div>

<div class="card">
<h2>Comanda <span id="num-comanda"></span></h2>
<div id="lista-comanda"></div>
<div>Total: <b id="total1">R$ 0,00</b></div>
<button class="btn ok" onclick="irTela2()">Finalizar Pedido</button>
</div>

</div>

<div id="tela2" class="grid" style="display:none;">

<div class="card">
<h2>Comanda</h2>
<div id="resumo-comanda"></div>

<h3 style="margin-top:15px;">Nota Fiscal</h3>
<div id="nota"></div>
</div>

<div class="card">
<h2>Pagamento</h2>

<div>Total: <b id="total2">R$ 0,00</b></div>

<select id="forma_pagamento">
<option value="">Selecione</option>
<option>Dinheiro</option>
<option>Cartão</option>
<option>Pix</option>
<option>Cheque</option>
</select>

<div id="parcelas-area" style="display:none;">
<select id="parcelas">
<option value="1">1x</option>
<option value="2">2x</option>
<option value="3">3x</option>
<option value="4">4x</option>
<option value="5">5x</option>
<option value="6">6x</option>
<option value="7">7x</option>
<option value="8">8x</option>
<option value="9">9x</option>
<option value="10">10x</option>
<option value="11">11x</option>
<option value="12">12x</option>
</select>
<div id="valor-parcela"></div>
</div>

<input type="number" id="valor_pago" placeholder="Valor pago">
<div>Troco: <b id="troco">R$ 0,00</b></div>

<div id="msg"></div>

<button class="btn ok" onclick="finalizar()" ondblclick="irFinanceiro()">Finalizar Venda</button>

</div>
</div>

<div id="tela3" class="grid" style="display:none;">
<div class="card">
<h2>Financeiro</h2>

<div>Investimento atual:</div>
<b id="f_investido"></b>

<br><br>

<div>Meta (investimento + 2000):</div>
<b id="f_meta"></b>

<br><br>

<div>Total em caixa:</div>
<b id="f_total"></b>

<br><br>

<div>Resultado:</div>
<b id="f_resultado"></b>

<br><br>

<button onclick="resetar()">Finalizar</button>

</div>
</div>

</div>

<script>
let carrinho=[];
let totalInvestido=<?php echo $custo_total; ?>;
let caixa=0;

let comanda=Math.floor(Math.random()*1000);
document.getElementById('num-comanda').innerText='#'+comanda;

function dinheiro(v){return 'R$ '+v.toFixed(2).replace('.',',');}

function atualizar(){
let html='',total=0;
carrinho.forEach(i=>{
let sub=i.preco*i.qtd;
total+=sub;
html+=i.nome+" x"+i.qtd+" - "+dinheiro(sub)+"<br>";
});
document.getElementById('lista-comanda').innerHTML=html;
document.getElementById('total1').innerText=dinheiro(total);
}

function addCarrinho(c,n,p,ct){
let est=document.getElementById('est_'+c);
if(parseInt(est.innerText)<=0)return alert('Sem estoque');
est.innerText=parseInt(est.innerText)-1;
let i=carrinho.find(x=>x.cod==c);
if(i)i.qtd++; else carrinho.push({cod:c,nome:n,preco:p,custo:ct,qtd:1});
atualizar();
}

function irTela2(){
if(carrinho.length==0)return;

document.getElementById('tela1').style.display='none';
document.getElementById('tela2').style.display='grid';

let total=0,resumo="";

carrinho.forEach(i=>{
let sub=i.preco*i.qtd;
total+=sub;
resumo+=i.nome+" x"+i.qtd+" - "+dinheiro(sub)+"<br>";
});

document.getElementById('resumo-comanda').innerHTML=resumo;
document.getElementById('nota').innerHTML="";
document.getElementById('total2').innerText=dinheiro(total);
}

document.getElementById('forma_pagamento').addEventListener('change',function(){
let f=this.value;
let area=document.getElementById('parcelas-area');
let campo=document.getElementById('valor_pago');

if(f==="Cartão" || f==="Pix"){
area.style.display='block';
calcularParcelas();
}else{
area.style.display='none';
campo.readOnly=false;
campo.value='';
document.getElementById('valor-parcela').innerText='';
}
});

document.getElementById('parcelas').addEventListener('change',calcularParcelas);

function calcularParcelas(){
let total=parseFloat(document.getElementById('total2').innerText.replace('R$ ','').replace(',','.'))||0;
let p=parseInt(document.getElementById('parcelas').value);
let valor=total/p;

document.getElementById('valor-parcela').innerText=p+"x de "+dinheiro(valor);

let campo=document.getElementById('valor_pago');
campo.value=valor.toFixed(2);
campo.readOnly=true;
document.getElementById('troco').innerText='R$ 0,00';
}

document.getElementById('valor_pago').addEventListener('input',function(){
let f=document.getElementById('forma_pagamento').value;
if(f==="Cartão" || f==="Pix")return;

let total=parseFloat(document.getElementById('total2').innerText.replace('R$ ','').replace(',','.'))||0;
let pago=parseFloat(this.value)||0;
let troco=pago-total;

document.getElementById('troco').innerText=troco>0?dinheiro(troco):'R$ 0,00';
});

function finalizar(){

let forma=document.getElementById('forma_pagamento').value;

if(!forma){
document.getElementById('msg').innerText='Escolha a forma de pagamento';
return;
}

let total=0,custo=0,notaHtml="";

carrinho.forEach(i=>{
let sub=i.preco*i.qtd;
total+=sub;
custo+=i.custo*i.qtd;
notaHtml+=i.nome+" x"+i.qtd+" - "+dinheiro(sub)+"<br>";
});

caixa+=total;
totalInvestido-=custo;

document.getElementById('nota').innerHTML = `
<b>Comanda:</b> #${comanda}<br><br>
${notaHtml}
<br><b>Total:</b> ${dinheiro(total)}
`;

document.getElementById('msg').innerText='Venda finalizada';
}

function irFinanceiro(){

let meta=totalInvestido+2000;
let resultado=caixa-totalInvestido;

document.getElementById('tela2').style.display='none';
document.getElementById('tela3').style.display='grid';

document.getElementById('f_investido').innerText=dinheiro(totalInvestido);
document.getElementById('f_meta').innerText=dinheiro(meta);
document.getElementById('f_total').innerText=dinheiro(caixa);

if(resultado>=0){
document.getElementById('f_resultado').innerText="Lucro: "+dinheiro(resultado);
}else{
document.getElementById('f_resultado').innerText="Prejuízo: "+dinheiro(resultado);
}
}

function resetar(){
carrinho=[];

document.getElementById('tela3').style.display='none';
document.getElementById('tela1').style.display='grid';

document.getElementById('lista-comanda').innerHTML='';
document.getElementById('total1').innerText='R$ 0,00';

let comanda=Math.floor(Math.random()*1000);
document.getElementById('num-comanda').innerText='#'+comanda;
}
</script>

</body>
</html>