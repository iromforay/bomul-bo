// 범용 드래그앤드랍 업로드 위젯 - temp 업로드 방식.
// 드롭 즉시 서버는 customer/temp/에만 저장하고, 실제 소유 경로로 옮기는 건 폼 저장(POST) 시점에
// 서버가 처리한다. 그래서 이 위젯은 customer_idx 같은 소유자 식별자 없이도 동작하고, 등록(신규 가입)
// 화면이든 나중에 만들 사용자(고객) 페이지든 아래 마크업 규칙만 맞추면 그대로 재사용 가능.
//
// 필요한 마크업 (data-* 는 CSS selector 문자열):
// <div class="upload-drop" data-upload-url="/Customer/uploadTemp" data-file-type="biz"
//      data-path-target="#biz_reg_file_path" data-name-target="#biz_reg_file_name"
//      data-del-target="#del_biz_reg_img">
//     <input type="file" accept=".png,.jpg,.jpeg,.pdf">
//     <div class="upload-hint">클릭하거나 파일을 끌어다 놓으세요</div>
//     <div class="upload-preview"></div>
//     <button type="button" class="upload-remove" hidden>삭제</button>
// </div>
// <input type="hidden" id="biz_reg_file_path" name="biz_reg_file_path" value="">
// <input type="hidden" id="biz_reg_file_name" name="biz_reg_file_name" value="">
// <input type="hidden" id="del_biz_reg_img" name="del_img[]" value="biz" disabled>
(function () {
    function isImageName(name) {
        return /\.(png|jpe?g|gif|webp)$/i.test(name || '');
    }

    function renderPreview(preview, url, label) {
        preview.innerHTML = '';
        if (url && isImageName(label)) {
            var img = document.createElement('img');
            img.src = url;
            img.className = 'upload-thumb';
            preview.appendChild(img);
        }
        if (label) {
            var span = document.createElement('span');
            span.textContent = label;
            preview.appendChild(span);
        }
    }

    function initDropzone(zone) {
        var uploadUrl = zone.dataset.uploadUrl;
        var type = zone.dataset.fileType || '';
        var fileInput = zone.querySelector('input[type=file]');
        var preview = zone.querySelector('.upload-preview');
        var removeBtn = zone.querySelector('.upload-remove');
        var pathInput = zone.dataset.pathTarget ? document.querySelector(zone.dataset.pathTarget) : null;
        var nameInput = zone.dataset.nameTarget ? document.querySelector(zone.dataset.nameTarget) : null;
        var delInput = zone.dataset.delTarget ? document.querySelector(zone.dataset.delTarget) : null;

        if (!fileInput || !uploadUrl) return;

        function setBusy(busy) {
            zone.classList.toggle('is-uploading', busy);
        }

        function uploadFile(file) {
            var fd = new FormData();
            fd.append('file', file);
            fd.append('type', type);

            setBusy(true);
            fetch(uploadUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    setBusy(false);
                    if (res.SUCCESS !== 'TRUE') {
                        alert(res.MSG || '업로드에 실패했습니다.');
                        return;
                    }
                    var rec = res.RECORD || {};
                    if (pathInput) pathInput.value = rec.file_path || '';
                    if (nameInput) nameInput.value = rec.file_name || '';
                    // temp 파일이 새로 올라왔으니 삭제 예약은 취소
                    if (delInput) { delInput.disabled = true; }

                    renderPreview(preview, rec.temp_url, rec.file_name_original || rec.file_name);
                    zone.classList.add('has-file');
                    if (removeBtn) removeBtn.hidden = false;
                })
                .catch(function () {
                    setBusy(false);
                    alert('업로드 중 오류가 발생했습니다.');
                });
        }

        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('is-dragover');
        });
        zone.addEventListener('dragleave', function () {
            zone.classList.remove('is-dragover');
        });
        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('is-dragover');
            if (e.dataTransfer.files.length) uploadFile(e.dataTransfer.files[0]);
        });
        zone.addEventListener('click', function (e) {
            if (e.target === fileInput || e.target === removeBtn) return;
            fileInput.click();
        });
        fileInput.addEventListener('change', function () {
            if (fileInput.files.length) uploadFile(fileInput.files[0]);
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                // 이미 저장된 파일이었으면 del 히든을 활성화해서 폼 저장 시 서버가 실삭제하도록 예약.
                // 방금 올린 temp 파일이었으면 저장 전이라 서버엔 아직 안 걸려있어 로컬 상태만 비우면 된다.
                if (delInput) delInput.disabled = false;
                if (pathInput) pathInput.value = '';
                if (nameInput) nameInput.value = '';
                preview.innerHTML = '';
                zone.classList.remove('has-file');
                removeBtn.hidden = true;
            });
        }
    }

    document.querySelectorAll('.upload-drop').forEach(initDropzone);
})();
