<?php
/*------------------------------------------------------------------------
	# SM Listing Deals- Version 1.0.0
	# Copyright (c) 2015 YouTech Company. All Rights Reserved.
	# @license - Copyrighted Commercial Software
	# Author: YouTech Company
	# Websites: http://www.magentech.com
   -------------------------------------------------------------------------*/?>

<script type="text/javascript">
//<![CDATA[
data = new Date(2013,10,26,12,00,00);
var listdeal = [];
function CountDown(date,id){
	dateNow = new Date();
	amount = date.getTime() - dateNow.getTime();
	delete dateNow;
	if(amount < 0){
		document.getElementById(id).innerHTML="Now!";
	} else{
		days=0;hours=0;mins=0;secs=0;out="";
		amount = Math.floor(amount/1000);
		days=Math.floor(amount/86400);
		amount=amount%86400;
		hours=Math.floor(amount/3600);
		amount=amount%3600;
		mins=Math.floor(amount/60);
		amount=amount%60;
		secs=Math.floor(amount);
		if(days != 0){out += "<div class='time-item time-day'>" + "<div class='num-time'>" + days + "</div>" +" <div class='name-time'>"+((days==1)?"Day":"Days") + "</div>"+"</div> ";}
		if(hours != 0){out += "<div class='time-item time-hour'>" + "<div class='num-time'>" + hours + "</div>" +" <div class='name-time'>"+((hours==1)?"Hour":"Hours") + "</div>"+"</div> ";}
		out += "<div class='time-item time-min'>" + "<div class='num-time'>" + mins + "</div>" +" <div class='name-time'>"+((mins==1)?"Min":"Mins") + "</div>"+"</div> ";
		out += "<div class='time-item time-sec'>" + "<div class='num-time'>" + secs + "</div>" +" <div class='name-time'>"+((secs==1)?"Sec":"Secs") + "</div>"+"</div> ";
		out = out.substr(0,out.length-2);
		document.getElementById(id).innerHTML=out;
		setTimeout(function(){CountDown(date,id)}, 1000);
	}
}
//]]>
</script>   
 
<?php  
$helper = Mage::helper('listingdeals/data');
if ($this->_isAjax()) {
	$ajax_listingtags_start = $this->getRequest()->getPost('ajax_listingtags_start');
	$j = $ajax_listingtags_start;
	$catid = $this->getRequest()->getPost('categoryid');
	$catid = $this->getRequest()->getPost('categoryid');
	$child_items = $this->_getProductInfor($catid);
}

if (!empty($child_items)) {
    $k = $this->getRequest()->getPost('ajax_listingtags_start', 0);
    foreach ($child_items as $_product) {
		$time_id = rand() . time();
		$specialprice = $_product->getData('special_price');
		$specialPriceFromDate = $_product->getData('special_from_date');
		$specialPriceToDate = $_product->getData('special_to_date');
		$today =  time();
		
		$now = date("Y-m-d H:m:s");
		$newsFrom= $_product->getNewsFromDate();
		$newsTo= $_product->getNewsToDate();
		
		if ($specialprice && $specialPriceFromDate && $specialPriceToDate){
			if( $today >= strtotime( $specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime( $specialPriceFromDate) && is_null($specialPriceToDate) ){ 		
        $k++; ?>
        <div class="ltabs-item new-ltabs-item item">
            <div class="item-inner">
                <?php if ($_product->_image) { ?>
                    <div class="product-image">
                        <a href="<?php echo $_product->link ?>" <?php echo $helper->parseTarget($this->_getConfig('product_links_target', '_self')) ?>
                           title="<?php echo $_product->title ?>">
                            <img title="<?php echo $_product->title; ?>" alt="<?php echo $_product->title; ?>" src="<?php echo $_product->_image; ?>"/>
                        </a>
						<?php if ( $now>=$newsFrom && $now<=$newsTo ){ ?>
							<span class="new-product have-ico"><?php echo $this->__('New'); ?></span>
						<?php }
						if ( $specialprice ){ ?>
							<span class="sale-product have-ico"><?php echo $this->__('Sale'); ?></span>
						<?php } ?>							
                    </div>
                <?php } ?>
				
				<div class="product-info">
					<?php if ($this->_getConfig('product_title_display', 1) == 1) { ?>
						<div class="product-name">
							<a href="<?php echo $_product->link ?>" <?php echo $helper->parseTarget($this->_getConfig('product_links_target', '_self')) ?>
							   title="<?php echo $_product->title ?>">
								<?php echo $helper->truncate($_product->title, $this->_getConfig('product_title_maxlength', 25)); ?>
							</a>
						</div>
					<?php } ?>	
					<?php if ($this->_getConfig('product_description_display', 1) == 1 && $helper->_trimEncode($_product->_description) != '') { ?>
						<div class="product-desc">
							<?php echo $_product->_description; ?>
						</div>
					<?php } ?>	
					<?php if ((int)$this->_getConfig('product_reviews_count', 1)) { ?>
						<div class="product-review">
							<?php echo $this->getReviewsSummaryHtml($_product,'short', true, true); ?>
						</div>
					<?php } ?>	
					<?php if ((int)$this->_getConfig('product_price_display', 1)) { ?>
						<div class="product-price">
							<?php echo $this->getPriceHtml($_product, true); ?>
						</div>
					<?php } ?>
					
					<div class="item-time">	
						<div class="item-timer" id="product_time_<?php echo $time_id;?>"></div>	
						<script type="text/javascript">
						//<![CDATA[
							listdeal.push('product_time_<?php echo $time_id."&&||&&".date("Y/m/d", strtotime($specialPriceToDate));?>') ;
						//]]>
						</script>											
					</div>					
					
					<div class="product-addto-wrap">
						<?php if ((int)$this->_getConfig('product_addcart_display', 1)) { ?>
							<div class="product-addcart">
								<?php if($_product->isSaleable()): ?>
									<a class="btn-cart" title="<?php echo $this->__('Add to cart') ?>" href="javascript:void(0);" onclick="setLocation('<?php echo $this->getAddToCartUrl($_product) ?>')">
										<?php echo $this->__('Add to Cart') ?>
									</a>
								<?php else: ?>
								<p class="availability out-of-stock">
									<span><?php echo $this->__('Out of stock') ?> </span>
								</p>
								<?php endif; ?>								
							</div>
						<?php } ?>
						
						<?php if ((int)$this->_getConfig('product_addwishlist_display', 1) || (int)$this->_getConfig('product_addcompare_display', 1)) { ?>
							<div class="wishlist-compare">
								<?php if ( $this->helper('wishlist')->isAllow() && (int)$this->_getConfig('product_addwishlist_display', 1) ) : ?>
								<a class="link-wishlist" href="<?php echo $this->helper('wishlist')->getAddUrl($_product) ?>" title="<?php echo $this->__('Add to Wishlist') ?>">
									<?php echo $this->__('Add to Wishlist') ?>
								</a>
								<?php endif; ?>
				
								<?php if( $_compareUrl = $this->getAddToCompareUrl($_product) && (int)$this->_getConfig('product_addcompare_display', 1) ): ?>
								<a class="link-compare" href="<?php echo $_compareUrl ?>" title="<?php echo $this->__('Add to Compare'); ?>">
									<?php echo $this->__('Add to Compare') ?>
								</a>
								<?php endif;?>
							</div>
						<?php } ?>								
					</div>					
				</div>
            </div>
        </div>
		<?php }}?>

    <?php
    }
}?>
<script type="text/javascript">
//<![CDATA[
//window.onload=function(){
jQuery(document).ready(function () {
	if(listdeal.length > 0){
		for(i=0;i<listdeal.length;i++)
		{
			var arr = listdeal[i].split("&&||&&"); 
			var data = new Date(arr[1]);
			CountDown(data, arr[0]);
		}	
	}	
});
//]]>
</script>	
