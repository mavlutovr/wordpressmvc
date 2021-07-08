
<style>
.wdpro--substrate {
  background: #EEE;
  padding: 20px;
  height: 100%;
  font-family: Verdana;
}

.wdpro--signature,
.wdpro--content-block {
  padding: 30px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.wdpro--content-block {
  background: white;
  border-radius: 8px;
}

.wdpro--signature {
  font-size: 0.75em;
  color: #777;
}

.wdpro--signature a {
  color: #777;
}

.wdpro--substrate img {
  max-width: 100%;
  border-radius: 5px;
}
</style>



<div class="wdpro--substrate">

  <div class="wdpro--content-block">
    <?= $data['content'] ?>
  </div>

  <div class="wdpro--signature">
    <?= $data['signature'] ?>
  </div>

</div>


