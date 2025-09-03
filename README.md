# README
## 1) EC2 準備（インスタンス／キーペア／セキュリティグループ）

* **Launch instance** を押して作成します。

  * **AMI**: Amazon Linux 2023
  * **Instance type**: t3.micro（相当で可）
  * **Key pair**: .pemを作成
  * **Network settings**:

    * **Auto-assign public IP**: 有効（Enable）
* **Launch** で起動し、**Public IPv4 address** を控える

  * 任意: IPを固定したい場合は **Elastic IP** を割り当てます。

---

## 2) SSH 接続（OS別の例）

* **Windows / CMD**: `ssh -i C:\Users\Desktop\既存.pem ec2-user@<PublicIP>`

## 3) Docker / Docker Compose のインストール（Amazon Linux 2023）

```bash
sudo dnf -y update || sudo yum -y update
sudo dnf -y install docker docker-compose-plugin || sudo yum -y install docker
sudo systemctl enable --now docker
sudo usermod -aG docker ec2-user

# 必要ツール
sudo dnf -y install curl

# アーキ判定（x86_64 / aarch64）
ARCH=$(uname -m)
case "$ARCH" in
  x86_64)   BIN=docker-compose-linux-x86_64 ;;
  aarch64)  BIN=docker-compose-linux-aarch64 ;;
  *) echo "Unsupported arch: $ARCH"; exit 1 ;;
esac

# プラグイン配置（システム全体）
sudo mkdir -p /usr/libexec/docker/cli-plugins
sudo curl -L "https://github.com/docker/compose/releases/download/v2.27.0/$BIN" \
  -o /usr/libexec/docker/cli-plugins/docker-compose
sudo chmod +x /usr/libexec/docker/cli-plugins/docker-compose

# 反映: 一度 exit → 再SSH もしくは newgrp docker
exit
# ローカルPCから再接続
ssh -i <鍵.pem> ec2-user@<PublicIP>

# 動作確認
docker --version
docker compose version
```

---

## 4) ソースコードの取得（Git から持ってくる）

```bash
1) Git を入れる（Amazon Linux 2023）
sudo dnf -y install git || sudo yum -y install git
git --version


cd ~ && rm -rf web
# HTTPS 方式
git clone https://github.com/rrroko/webkadai.git
cd ~/webkadai
```

---

## 5) 事前準備（権限）

```bash
sudo chown -R 82:82 upload/image   # または: chmod 777 upload/image
```

---

## 6) ビルド＆起動（授業仕様：80 番公開）

```bash
# compose.yml の ports が ["80:80"] であることを確認

docker compose build
docker compose up -d
# 状態確認
docker compose ps
```


## 7) テーブル作成（init.sql を適用）

```bash
docker compose exec -T mysql \
  mysql -uappuser -papppass appdb < init.sql
```

## 8) 動作確認

* EC2 内: `curl -I http://localhost/` → `HTTP/1.1 200 OK`
* 外部: ブラウザで `http://<PublicIP>/` にアクセス
