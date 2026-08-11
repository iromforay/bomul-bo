<?php
$msg = session()->getFlashdata('msg');
$err = session()->getFlashdata('err');

$billingLabels = ['MONTHLY' => '월구독', 'ONCE' => '1회결제', 'NONE' => '미결제'];
$typeLabels = ['BASE' => '기본상품', 'ADDON' => '부가서비스'];
?>
<div class="crumb">HOME &nbsp;›&nbsp; 상품관리 &nbsp;›&nbsp; <b>상품 목록</b></div>

<div class="page-head">
    <h1>상품(서비스) 관리</h1>
    <div class="actions">
        <a href="<?= Admin_SITE ?>/Product/write"><button class="primary">+ 상품 등록</button></a>
    </div>
</div>

<?= view('common/admin_alert', ['msg' => $msg, 'err' => $err]) ?>

<div class="panel">
    <div class="panel-head">
        <h2>전체 상품</h2>
    </div>
    <table>
        <thead>
        <tr>
            <th>번호</th><th>유형</th><th>상품코드</th><th>상품명</th><th>기간</th><th>가격</th><th>결제방식</th>
            <th>이벤트</th><th>상태</th><th>정렬</th><th>관리</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($list)): ?>
            <tr><td colspan="11" style="text-align:center; color:#6B7A88;">등록된 상품이 없습니다.</td></tr>
        <?php else: ?>
            <?php foreach ($list as $row): ?>
                <tr>
                    <td><?= (int) $row['product_no'] ?></td>
                    <td><?= htmlspecialchars($typeLabels[$row['product_type']] ?? $row['product_type'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['product_code'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?></td>
                    <td><span class="badge wait"><?= htmlspecialchars(product_period_label($row['billing_type'], $row['period_day']), ENT_QUOTES) ?></span></td>
                    <td>
                        <?php if (!empty($row['list_price']) && (int) $row['list_price'] > (int) $row['price']): ?>
                            <span style="text-decoration:line-through; color:#6B7A88;"><?= number_format((int) $row['list_price']) ?>원</span><br>
                        <?php endif; ?>
                        <?= number_format((int) $row['price']) ?>원
                    </td>
                    <td><?= htmlspecialchars($billingLabels[$row['billing_type']] ?? $row['billing_type'], ENT_QUOTES) ?></td>
                    <td><?= ((string) $row['is_event']) === '1' ? '<span class="badge wait">이벤트</span>' : '-' ?></td>
                    <td>
                        <?php if ($row['status'] === 'USE'): ?>
                            <span class="badge done">사용</span>
                        <?php else: ?>
                            <span class="badge stop">중지</span>
                        <?php endif; ?>
                    </td>
                    <td><?= (int) $row['sort'] ?></td>
                    <td>
                        <a href="<?= Admin_SITE ?>/Product/modify/<?= (int) $row['product_no'] ?>">수정</a>
                        &nbsp;|&nbsp;
                        <form method="post" action="<?= Admin_SITE ?>/Product/delete/<?= (int) $row['product_no'] ?>" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까? 신청 이력이 있으면 삭제되지 않습니다.');">
                            <button type="submit" style="border:none; background:none; color:#B3261E; cursor:pointer; padding:0; font-size:12px;">삭제</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
