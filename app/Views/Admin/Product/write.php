<div class="crumb">HOME &nbsp;›&nbsp; 상품관리 &nbsp;›&nbsp; <b>상품 등록</b></div>

<div class="page-head">
    <h1>상품 등록</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>상품 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/Product/write" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>상품유형</th>
                <td>
                    <select name="product_type">
                        <option value="BASE" selected>기본상품 (예: 중개사 사이트 개설)</option>
                        <option value="ADDON">부가서비스 (예: 디자인 템플릿 적용)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>상품코드</th>
                <td><input type="text" name="product_code" required></td>
            </tr>
            <tr>
                <th>상품명</th>
                <td>
                    <input type="text" name="product_name" placeholder="예: 중개사 사이트 개설" required>
                    <p style="color:#6B7A88; font-size:12px; margin-top:4px;">기간(1개월/구독형 등)은 아래 결제방식·이용기간으로 별도 관리되니 상품명엔 넣지 마세요.</p>
                </td>
            </tr>
            <tr>
                <th>판매가(원)</th>
                <td><input type="number" name="price" min="0" value="0" required></td>
            </tr>
            <tr>
                <th>정가(원)</th>
                <td>
                    <input type="number" name="list_price" min="0" placeholder="할인 강조용, 비워두면 미표시">
                    <p style="color:#6B7A88; font-size:12px; margin-top:4px;">판매가보다 크게 입력하면 화면에 취소선 정가+판매가로 표시됩니다. 할인이 없으면 비워두세요.</p>
                </td>
            </tr>
            <tr>
                <th>결제방식</th>
                <td>
                    <select name="billing_type">
                        <option value="MONTHLY">월구독(자동연장)</option>
                        <option value="ONCE">1회결제</option>
                        <option value="NONE">미결제(이벤트/무통장)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>이용기간(일)</th>
                <td>
                    <input type="number" name="period_day" min="1" value="30" required>
                    <span style="color:#6B7A88; font-size:12px; margin-left:8px;">30=1개월, 180=6개월, 365=12개월로 자동 라벨 표시됨</span>
                </td>
            </tr>
            <tr>
                <th>이벤트 상품</th>
                <td>
                    <select name="is_event">
                        <option value="0">일반</option>
                        <option value="1">이벤트(고객당 1회만 신청 가능)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>상태</th>
                <td>
                    <select name="status">
                        <option value="USE" selected>사용</option>
                        <option value="STOP">중지</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>정렬순서</th>
                <td><input type="number" name="sort" value="0"></td>
            </tr>
            <tr>
                <th>설명</th>
                <td><textarea name="description" rows="4" style="width:100%;"></textarea></td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">등록</button>
            <a href="<?= Admin_SITE ?>/Product"><button type="button">취소</button></a>
        </div>
    </form>
</div>
