<div class="crumb">HOME &nbsp;›&nbsp; 상품관리 &nbsp;›&nbsp; <b>상품 수정</b></div>

<div class="page-head">
    <h1>상품 수정</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>상품 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/Product/modify/<?= (int) $row['product_no'] ?>" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>상품코드</th>
                <td><input type="text" name="product_code" value="<?= htmlspecialchars($row['product_code'], ENT_QUOTES) ?>" required></td>
            </tr>
            <tr>
                <th>상품명</th>
                <td><input type="text" name="product_name" value="<?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?>" required></td>
            </tr>
            <tr>
                <th>가격(원)</th>
                <td><input type="number" name="price" min="0" value="<?= (int) $row['price'] ?>" required></td>
            </tr>
            <tr>
                <th>결제방식</th>
                <td>
                    <select name="billing_type">
                        <option value="MONTHLY" <?= $row['billing_type'] === 'MONTHLY' ? 'selected' : '' ?>>월구독</option>
                        <option value="ONCE" <?= $row['billing_type'] === 'ONCE' ? 'selected' : '' ?>>1회결제</option>
                        <option value="NONE" <?= $row['billing_type'] === 'NONE' ? 'selected' : '' ?>>미결제(이벤트/무통장)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>이용기간(일)</th>
                <td><input type="number" name="period_day" min="1" value="<?= (int) $row['period_day'] ?>" required></td>
            </tr>
            <tr>
                <th>이벤트 상품</th>
                <td>
                    <select name="is_event">
                        <option value="0" <?= ((string) $row['is_event']) !== '1' ? 'selected' : '' ?>>일반</option>
                        <option value="1" <?= ((string) $row['is_event']) === '1' ? 'selected' : '' ?>>이벤트(고객당 1회만 신청 가능)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>상태</th>
                <td>
                    <select name="status">
                        <option value="USE" <?= $row['status'] === 'USE' ? 'selected' : '' ?>>사용</option>
                        <option value="STOP" <?= $row['status'] === 'STOP' ? 'selected' : '' ?>>중지</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>정렬순서</th>
                <td><input type="number" name="sort" value="<?= (int) $row['sort'] ?>"></td>
            </tr>
            <tr>
                <th>설명</th>
                <td><textarea name="description" rows="4" style="width:100%;"><?= htmlspecialchars($row['description'] ?? '', ENT_QUOTES) ?></textarea></td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">저장</button>
            <a href="<?= Admin_SITE ?>/Product"><button type="button">취소</button></a>
        </div>
    </form>
</div>
