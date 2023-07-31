const express = require('express');
const AWS = require('aws-sdk');
const app = express();

AWS.config.update({
  accessKeyId: 'AKIA6GY63CLQL4P7VTP4',
  secretAccessKey: '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',
  region: 'ap-northeast-2'
});

// S3 객체 생성
const s3 = new AWS.S3();

// 이미지 목록 가져오기
app.get('/images', (req, res) => {
  const params = {
    Bucket: 'semstestbucket',
    Prefix: 'images/' // 이 부분은 필요에 따라 변경 가능
  };

  s3.listObjects(params, (err, data) => {
    if (err) {
      console.error(err);
      res.status(500).json({ error: 'Failed to fetch images' });
    } else {
      const images = data.Contents.map((obj) => {
        return {
          url: `https://YOUR_S3_BUCKET_URL/${obj.Key}`,
          name: obj.Key
        };
      });
      res.json(images);
    }
  });
});

const port = 3000;
app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});
