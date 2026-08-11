<div class="crumb">HOME &nbsp;›&nbsp; 게시판 &nbsp;›&nbsp; <?= htmlspecialchars($typeLabel, ENT_QUOTES) ?> &nbsp;›&nbsp; <b>글쓰기</b></div>

<div class="page-head">
    <h1><?= htmlspecialchars($typeLabel, ENT_QUOTES) ?> 글쓰기</h1>
</div>

<div class="panel">
    <div class="panel-head"><h2>글 정보</h2></div>
    <form method="post" action="<?= Admin_SITE ?>/Board/<?= $type ?>/write" style="padding:16px;">
        <table class="form-table">
            <tbody>
            <tr>
                <th>제목</th>
                <td><input type="text" name="title" required></td>
            </tr>
            <tr>
                <th>내용</th>
                <td><textarea name="content" rows="8" style="width:100%;" required></textarea></td>
            </tr>
            <tr>
                <th>작성자명</th>
                <td><input type="text" name="writer_name" placeholder="비워두면 '관리자'로 표시"></td>
            </tr>
            <tr>
                <th>작성자 연락처</th>
                <td><input type="text" name="writer_phone" placeholder="비회원 상담문의 등 콜백 연락처"></td>
            </tr>
            <tr>
                <th>처리상태</th>
                <td>
                    <select name="status">
                        <option value="WAIT" selected>답변대기</option>
                        <option value="DONE">답변완료</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>답변내용</th>
                <td><textarea name="reply_content" rows="5" style="width:100%;" placeholder="답변이 있을 때만 입력"></textarea></td>
            </tr>
            <tr>
                <th>노출여부</th>
                <td>
                    <select name="use_yn">
                        <option value="Y" selected>노출</option>
                        <option value="N">숨김</option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="form-actions">
            <button type="submit" class="primary">등록</button>
            <a href="<?= Admin_SITE ?>/Board/<?= $type ?>"><button type="button">취소</button></a>
        </div>
    </form>
</div>
