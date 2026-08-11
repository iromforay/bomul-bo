<div class="crumb">HOME &nbsp;›&nbsp; 게시판 &nbsp;›&nbsp; <?= htmlspecialchars($typeLabel, ENT_QUOTES) ?> &nbsp;›&nbsp; <b>글 수정</b></div>

<div class="page-head">
    <h1><?= htmlspecialchars($typeLabel, ENT_QUOTES) ?> 글 수정</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>글 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/Board/<?= $type ?>/modify/<?= (int) $row['board_no'] ?>" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>제목</th>
                <td><input type="text" name="title" value="<?= htmlspecialchars($row['title'], ENT_QUOTES) ?>" required></td>
            </tr>
            <tr>
                <th>내용</th>
                <td><textarea name="content" rows="8" style="width:100%;" required><?= htmlspecialchars($row['content'], ENT_QUOTES) ?></textarea></td>
            </tr>
            <tr>
                <th>작성자명</th>
                <td><input type="text" name="writer_name" value="<?= htmlspecialchars($row['writer_name'] ?? '', ENT_QUOTES) ?>" placeholder="비워두면 '관리자'로 표시"></td>
            </tr>
            <tr>
                <th>처리상태</th>
                <td>
                    <select name="status">
                        <option value="WAIT" <?= ($row['status'] ?? 'WAIT') === 'WAIT' ? 'selected' : '' ?>>답변대기</option>
                        <option value="DONE" <?= ($row['status'] ?? 'WAIT') === 'DONE' ? 'selected' : '' ?>>답변완료</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>답변내용</th>
                <td>
                    <textarea name="reply_content" rows="5" style="width:100%;" placeholder="답변이 있을 때만 입력"><?= htmlspecialchars($row['reply_content'] ?? '', ENT_QUOTES) ?></textarea>
                    <?php if (!empty($row['replied_at'])): ?>
                        <div style="color:#6B7A88; font-size:12px; margin-top:4px;">답변일시: <?= htmlspecialchars((string) $row['replied_at'], ENT_QUOTES) ?></div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>노출여부</th>
                <td>
                    <select name="use_yn">
                        <option value="Y" <?= ($row['use_yn'] ?? 'Y') === 'Y' ? 'selected' : '' ?>>노출</option>
                        <option value="N" <?= ($row['use_yn'] ?? 'Y') === 'N' ? 'selected' : '' ?>>숨김</option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">저장</button>
            <a href="<?= Admin_SITE ?>/Board/<?= $type ?>"><button type="button">취소</button></a>
        </div>
    </form>
</div>
