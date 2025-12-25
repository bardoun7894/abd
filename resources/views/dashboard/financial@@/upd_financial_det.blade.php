                        <div class="row gx-5 mb-5" id='show_details'>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5" style='display:none'>
                                <label for="financial_detail_id" class="form-label required fs-6 fw-bold text-dark mb-3">ققققققققققققق</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                        type="text" name="financial_detail_id" id="financial_detail_id" readonly
                                        class="form-control fw-bold text-dark text-info form-control-solid" value="{{ $financial->financial_detail_id }}"
                                        data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20"
                                        placeholder="المبلغ المطلوب ">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="financial_month_val" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ
                                    المطلوب</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                        type="text" name="financial_month_val" id="financial_month_val" readonly
                                        class="form-control fw-bold text-dark text-info form-control-solid"
                                        data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20" value="{{ $financial->financial_month_val }}"
                                        placeholder="المبلغ المطلوب ">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="financial_month_pay" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ
                                    المدفوع</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                        type="text" name="financial_month_pay" id="financial_month_pay"  value="{{ $financial->financial_month_pay }}"
                                        class="form-control fw-bold text-dark text-info" oncahnge='calc_all_price()'
                                        oninput="calc_all_price()"
                                        data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20"
                                        placeholder="المبلغ المدفوع">
                                </div>
                            </div>
                            <div class="col-12 col-lg-2 col-md-12 col-sm-12 mb-5">
                                <label for="financial_month_remain" class="form-label required fs-6 fw-bold text-dark mb-3">المبلغ
                                    المتبقي</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text"><i
                                                class="far fa-id-card fa-fw text-dark"></i></span></div><input
                                        type="text" name="financial_month_remain" id="financial_month_remain" value="{{ $financial->financial_month_remain }}"
                                        class="form-control fw-bold text-dark text-info"
                                        data-inputmask="'alias' : 'decimal'" minlenght="1" maxlength="20" readonly im-insert="true"
                                        placeholder="المبلغ المتبقي ">
                                </div>
                            </div>

                            <div class=" col-12 col-lg-6 col-md-12 col-sm-12  mb-5">
                                <label for="note" class="  form-label fs-6 fw-bold text-dark mb-3">ملاحظة
                                </label>
                                <textarea name="note" row='1' class="form-control fw-bold" id="note" placeholder="ملاحظة">{{ $financial->note }}</textarea>
                            </div>
                        </div>
