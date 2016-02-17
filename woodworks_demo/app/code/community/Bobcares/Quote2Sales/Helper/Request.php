<?php

class Request {

    public $id = 0;
    public $customer_id = 0;
    public $comment = "";
    public $name = "";
    public $email = "";
    public $phone = "";
    public $seller_comment = "";
    public $product_id = 0;
    public $model = null;

    /**
     * @desc This method will save th new request and 
     * @returns the id on success and zero on failure.
     * @param $sendmail weather mail has to be sent or not
     */
    function create($sendmail = TRUE) {
        //$this->comment = preg_replace('/\D+/', '', $this->comment); //sanitise
        //$this->customer_id = preg_replace('/\D+/', '', $this->customer_id); //sanitise

        /* If customer id and comment exists */
        if ($this->customer_id && $this->comment) {
            // insert a new request...


            $model = Mage::getModel('quote2sales/request');
            $model->setData('customer_id', $this->customer_id);
            $model->setData('comment', $this->comment);
            $model->setData("name", $this->name);
            $model->setData("email", $this->email);
            $model->setData("phone", $this->phone);
            $model->setData("status", 'Waiting');
            $model->setData('product_id', $this->product_id);
            $model->save();

            return $model->getId();
        } else
            return 0;
    }

}
